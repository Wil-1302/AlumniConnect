<?php

namespace App\Domain\Catalogos\Services;

use App\Domain\Catalogos\Repositories\PadronRepository;
use App\Domain\Seguridad\Services\AuditoriaService;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

/**
 * RF-31: carga masiva del padrón institucional desde Excel o CSV.
 *
 * Separa dos tipos de error a propósito: un archivo con el formato
 * equivocado (columnas faltantes, vacío) detiene la importación entera
 * con una excepción; una fila puntual inválida dentro de un archivo por
 * lo demás correcto se omite y se reporta, sin detener el resto.
 */
class PadronService
{
    private const COLUMNAS_REQUERIDAS = ['dni', 'nombres', 'apellidos', 'anio_egreso', 'grado'];
    private const GRADOS_VALIDOS = ['bachiller', 'titulado'];

    public function __construct(
        private readonly PadronRepository $padron,
        private readonly AuditoriaService $auditoria,
    ) {
    }

    public function importar(UploadedFile $archivo, int $usuarioId): object
    {
        // Sin una clase Import, toCollection() devuelve las filas en crudo
        // (celdas indexadas por posición, no por nombre de columna); por
        // eso resolverColumnas() calcula esa posición leyendo la cabecera.
        $hojas = Excel::toCollection(null, $archivo)->toArray();
        $filas = $hojas[0] ?? [];

        if (count($filas) < 1) {
            throw new ReglaNegocioException('El archivo está vacío.');
        }

        $indices = $this->resolverColumnas(array_shift($filas));

        $omitidos = [];
        $porInsertar = [];
        $dniVistosEnArchivo = [];

        foreach ($filas as $numeroFila => $fila) {
            // Encabezado = fila 1; los datos empiezan en la fila 2 del archivo.
            $numeroFilaVisible = $numeroFila + 2;

            if ($this->filaVacia($fila)) {
                continue;
            }

            $datos = [
                'dni'         => trim((string) ($fila[$indices['dni']] ?? '')),
                'nombres'     => trim((string) ($fila[$indices['nombres']] ?? '')),
                'apellidos'   => trim((string) ($fila[$indices['apellidos']] ?? '')),
                'anio_egreso' => trim((string) ($fila[$indices['anio_egreso']] ?? '')),
                'grado'       => strtolower(trim((string) ($fila[$indices['grado']] ?? ''))),
            ];

            $motivo = $this->validarFila($datos);

            if ($motivo !== null) {
                $omitidos[] = ['fila' => $numeroFilaVisible, 'dni' => $datos['dni'], 'motivo' => $motivo];

                continue;
            }

            if (isset($dniVistosEnArchivo[$datos['dni']])) {
                $omitidos[] = [
                    'fila' => $numeroFilaVisible, 'dni' => $datos['dni'],
                    'motivo' => 'DNI duplicado dentro del mismo archivo.',
                ];

                continue;
            }

            if ($this->padron->existe($datos['dni'])) {
                $omitidos[] = [
                    'fila' => $numeroFilaVisible, 'dni' => $datos['dni'],
                    'motivo' => 'Ese DNI ya existe en el padrón.',
                ];

                continue;
            }

            $dniVistosEnArchivo[$datos['dni']] = true;
            $porInsertar[] = [
                'dni'         => $datos['dni'],
                'nombres'     => $datos['nombres'],
                'apellidos'   => $datos['apellidos'],
                'anio_egreso' => (int) $datos['anio_egreso'],
                'grado'       => $datos['grado'],
            ];
        }

        $importados = $this->padron->insertarMasivo($porInsertar);

        $this->auditoria->registrar($usuarioId, 'importar_padron', 'padron_egresados');

        return (object) [
            'importados'       => $importados,
            'omitidos'         => count($omitidos),
            'detalle_omitidos' => $omitidos,
        ];
    }

    /** @return array<string, int> Nombre de columna => índice numérico en la fila. */
    private function resolverColumnas(array $encabezado): array
    {
        $encabezado = array_map(
            fn ($columna) => strtolower(trim((string) $columna)),
            $encabezado
        );

        $indices = [];

        foreach (self::COLUMNAS_REQUERIDAS as $columna) {
            $posicion = array_search($columna, $encabezado, true);

            if ($posicion === false) {
                throw new ReglaNegocioException(
                    'El archivo debe tener las columnas: ' . implode(', ', self::COLUMNAS_REQUERIDAS) . '.'
                );
            }

            $indices[$columna] = $posicion;
        }

        return $indices;
    }

    private function filaVacia(array $fila): bool
    {
        return collect($fila)->every(fn ($valor) => trim((string) ($valor ?? '')) === '');
    }

    private function validarFila(array $datos): ?string
    {
        if (! preg_match('/^\d{8}$/', $datos['dni'])) {
            return 'El DNI debe tener exactamente 8 dígitos.';
        }

        if ($datos['nombres'] === '' || $datos['apellidos'] === '') {
            return 'Nombres y apellidos son obligatorios.';
        }

        if (! ctype_digit($datos['anio_egreso']) || (int) $datos['anio_egreso'] < 1980 || (int) $datos['anio_egreso'] > (int) date('Y') + 1) {
            return 'El año de egreso no es válido.';
        }

        if (! in_array($datos['grado'], self::GRADOS_VALIDOS, true)) {
            return 'El grado debe ser "bachiller" o "titulado".';
        }

        return null;
    }
}
