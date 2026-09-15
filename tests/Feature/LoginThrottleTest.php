<?php

namespace Tests\Feature;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Protección contra fuerza bruta en /login (limitador "login" registrado
 * en AppServiceProvider): máximo 5 intentos por minuto, por correo + IP.
 */
class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function intentar(string $email, string $password = 'clave-incorrecta')
    {
        return $this->from('/login')->post('/login', ['email' => $email, 'password' => $password]);
    }

    public function test_permite_hasta_cinco_intentos_fallidos_por_minuto(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $respuesta = $this->intentar('egresado@example.test');

            $respuesta->assertRedirect('/login');
            $respuesta->assertSessionHasErrors(['email' => 'Las credenciales ingresadas no son válidas.']);
        }
    }

    public function test_bloquea_el_sexto_intento_en_el_mismo_minuto(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->intentar('egresado@example.test');
        }

        $respuesta = $this->intentar('egresado@example.test');

        $respuesta->assertStatus(302);
        $respuesta->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos',
            session('errors')->first('email'),
        );
    }

    public function test_el_bloqueo_persiste_aunque_la_contrasena_del_sexto_intento_sea_correcta(): void
    {
        $usuario = Usuario::create([
            'email'         => 'valido@example.test',
            'password_hash' => Hash::make('password'),
            'rol'           => Usuario::ROL_EGRESADO,
            'activo'        => true,
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $this->intentar($usuario->email);
        }

        // Sexto intento, ahora con la contraseña correcta: el bloqueo actúa
        // antes de que el controlador llegue a validar credenciales.
        $respuesta = $this->intentar($usuario->email, 'password');

        $respuesta->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Demasiados intentos',
            session('errors')->first('email'),
        );
        $this->assertGuest();
    }

    public function test_el_limite_es_por_correo_ademas_de_por_ip(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->intentar('primero@example.test');
        }

        // Se agotó el límite de "primero@example.test", pero un correo
        // distinto desde la misma IP de pruebas todavía puede intentarlo:
        // la clave del limitador combina correo + IP, no solo la IP.
        $respuesta = $this->intentar('segundo@example.test');

        $respuesta->assertSessionHasErrors(['email' => 'Las credenciales ingresadas no son válidas.']);
    }
}
