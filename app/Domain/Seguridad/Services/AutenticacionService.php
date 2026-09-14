<?php

namespace App\Domain\Seguridad\Services;

use App\Domain\Seguridad\Models\Usuario;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Support\Facades\Auth;

/**
 * RF-03, RF-13: autenticación de egresados y de personal administrativo.
 */
class AutenticacionService
{
    public function __construct(
        private readonly AuditoriaService $auditoria,
    ) {
    }

    public function autenticar(string $email, string $password, bool $recordar = false): Usuario
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $recordar)) {
            throw new ReglaNegocioException('Las credenciales ingresadas no son válidas.');
        }

        /** @var Usuario $usuario */
        $usuario = Auth::user();

        if (! $usuario->activo) {
            Auth::logout();
            throw new ReglaNegocioException('Su cuenta se encuentra desactivada.');
        }

        $usuario->update(['ultimo_acceso' => now()]);

        if ($usuario->esAdministrador()) {
            $this->auditoria->registrar($usuario->id, 'inicio_sesion_admin');
        }

        return $usuario;
    }

    public function cerrarSesion(): void
    {
        Auth::logout();
    }
}
