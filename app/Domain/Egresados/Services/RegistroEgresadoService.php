<?php

namespace App\Domain\Egresados\Services;

use App\Domain\Catalogos\Repositories\PadronRepository;
use App\Domain\Egresados\Models\Egresado;
use App\Domain\Egresados\Repositories\EgresadoRepository;
use App\Domain\Seguridad\Models\Usuario;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Caso de uso CU-01: registrar un egresado en la plataforma.
 *
 * Aplica las reglas RN-01 (padrón), RN-02 (DNI único) y RN-08 (consentimiento).
 * No conoce el protocolo HTTP: recibe datos simples y devuelve una entidad.
 */
class RegistroEgresadoService
{
    public function __construct(
        private readonly EgresadoRepository $egresados,
        private readonly PadronRepository $padron,
    ) {
    }

    public function registrar(array $datos): Egresado
    {
        $this->validarConsentimiento($datos);
        $registroPadron = $this->validarPadron($datos['dni']);
        $this->validarCuentaInexistente($datos['dni']);

        return DB::transaction(function () use ($datos, $registroPadron) {
            $usuario = Usuario::create([
                'email'         => $datos['email'],
                'password_hash' => Hash::make($datos['password']),
                'rol'           => Usuario::ROL_EGRESADO,
                'activo'        => true,
            ]);

            return $this->egresados->crear([
                'usuario_id'        => $usuario->id,
                'dni'               => $datos['dni'],
                'nombres'           => $registroPadron->nombres,
                'apellidos'         => $registroPadron->apellidos,
                'anio_egreso'       => $registroPadron->anio_egreso,
                'grado'             => $registroPadron->grado,
                'telefono'          => $datos['telefono'] ?? null,
                'ciudad'            => $datos['ciudad'] ?? null,
                'perfil_visible'    => true,
                'consentimiento_en' => now(),
            ]);
        });
    }

    /** RN-08: sin aceptación expresa no se crea la cuenta. */
    private function validarConsentimiento(array $datos): void
    {
        if (empty($datos['consentimiento'])) {
            throw ReglaNegocioException::regla(
                'RN-08',
                'Debe aceptar la política de tratamiento de datos personales para continuar.'
            );
        }
    }

    /** RN-01: solo puede registrarse quien figura en el padrón institucional. */
    private function validarPadron(string $dni): object
    {
        $registro = $this->padron->buscarPorDni($dni);

        if ($registro === null) {
            throw ReglaNegocioException::regla(
                'RN-01',
                'El documento ingresado no figura en el padrón de egresados de la Escuela. '
                . 'Si considera que se trata de un error, comuníquese con Secretaría Académica.'
            );
        }

        return $registro;
    }

    /** RN-02: un documento solo puede estar asociado a una cuenta. */
    private function validarCuentaInexistente(string $dni): void
    {
        if ($this->egresados->existeDni($dni)) {
            throw ReglaNegocioException::regla(
                'RN-02',
                'Ya existe una cuenta registrada con este documento de identidad.'
            );
        }
    }
}
