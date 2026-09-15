<?php

namespace App\Domain\Catalogos\Repositories;

use App\Domain\Catalogos\Models\PadronEgresado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PadronRepository
{
    public function buscarPorDni(string $dni): ?PadronEgresado
    {
        return PadronEgresado::where('dni', $dni)->first();
    }

    public function existe(string $dni): bool
    {
        return PadronEgresado::where('dni', $dni)->exists();
    }

    /**
     * Listado paginado del padrón (RF-31), con una casilla calculada
     * "tiene_cuenta" que indica si ya existe una cuenta de egresado con
     * ese DNI. Se resuelve en una sola consulta con EXISTS, no con N+1.
     */
    public function paginado(?string $busqueda, int $porPagina = 20): LengthAwarePaginator
    {
        $consulta = PadronEgresado::query()
            ->selectRaw(
                'padron_egresados.*, exists (select 1 from egresados where egresados.dni = padron_egresados.dni) as tiene_cuenta'
            );

        $busqueda = trim((string) $busqueda);

        if ($busqueda !== '') {
            $consulta->where(function ($sub) use ($busqueda) {
                $sub->where('dni', 'like', "%{$busqueda}%")
                    ->orWhere('nombres', 'ilike', "%{$busqueda}%")
                    ->orWhere('apellidos', 'ilike', "%{$busqueda}%");

                if (ctype_digit($busqueda)) {
                    $sub->orWhere('anio_egreso', (int) $busqueda);
                }
            });
        }

        return $consulta->orderBy('apellidos')->orderBy('nombres')
            ->paginate($porPagina)
            ->appends(['busqueda' => $busqueda !== '' ? $busqueda : null]);
    }

    /**
     * Inserta solo los registros que no existan todavía (por DNI). Se
     * apoya en insertOrIgnore como respaldo ante una carrera entre dos
     * importaciones simultáneas; PadronService ya filtró los duplicados
     * que puede ver de antemano.
     *
     * @param  array<int, array{dni: string, nombres: string, apellidos: string, anio_egreso: int, grado: string}>  $filas
     * @return int Cantidad de filas realmente insertadas.
     */
    public function insertarMasivo(array $filas): int
    {
        if ($filas === []) {
            return 0;
        }

        return PadronEgresado::query()->insertOrIgnore($filas);
    }
}
