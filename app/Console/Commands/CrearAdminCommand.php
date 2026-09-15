<?php

namespace App\Console\Commands;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Reemplaza la creación manual de administradores en base de datos.
 * Es el comando para el primer acceso en producción (ver docs/despliegue.md).
 */
class CrearAdminCommand extends Command
{
    protected $signature = 'usuario:crear-admin';

    protected $description = 'Crea un usuario administrador principal, pidiendo correo y contraseña de forma interactiva';

    public function handle(): int
    {
        $email = $this->pedirCorreoValido();

        if ($email === null) {
            return self::FAILURE;
        }

        $password = $this->pedirContrasenaValida();

        if ($password === null) {
            return self::FAILURE;
        }

        $usuario = Usuario::create([
            'email'         => $email,
            'password_hash' => Hash::make($password),
            'rol'           => Usuario::ROL_ADMIN_PRINCIPAL,
            'activo'        => true,
        ]);

        $this->info("Administrador creado correctamente: {$usuario->email} (id {$usuario->id}).");

        return self::SUCCESS;
    }

    private function pedirCorreoValido(): ?string
    {
        for ($intento = 0; $intento < 3; $intento++) {
            $email = $this->ask('Correo electrónico del administrador');

            $errores = Validator::make(
                ['email' => $email],
                ['email' => ['required', 'email:rfc', 'max:150', 'unique:usuarios,email']]
            )->errors();

            if ($errores->isEmpty()) {
                return $email;
            }

            $this->error($errores->first('email'));
        }

        $this->error('Demasiados intentos. Vuelva a ejecutar el comando.');

        return null;
    }

    private function pedirContrasenaValida(): ?string
    {
        for ($intento = 0; $intento < 3; $intento++) {
            $password = $this->secret('Contraseña (mínimo 8 caracteres)');
            $confirmacion = $this->secret('Confirme la contraseña');

            if ($password !== $confirmacion) {
                $this->error('Las contraseñas no coinciden.');

                continue;
            }

            if (strlen((string) $password) < 8) {
                $this->error('La contraseña debe tener al menos 8 caracteres.');

                continue;
            }

            return $password;
        }

        $this->error('Demasiados intentos. Vuelva a ejecutar el comando.');

        return null;
    }
}
