<?php

namespace Tests\Feature;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * RF-03/RF-13: login con la casilla "Recordarme" marcada guarda el
 * remember_token en usuarios y emite la cookie de sesión persistente.
 */
class LoginRecordarTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): Usuario
    {
        return Usuario::create([
            'email' => 'egresado@example.test',
            'password_hash' => Hash::make('password'),
            'rol' => Usuario::ROL_EGRESADO,
            'activo' => true,
        ]);
    }

    public function test_inicia_sesion_y_guarda_remember_token_cuando_recordar_esta_marcado(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this->from('/login')->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
            'recordar' => '1',
        ]);

        $respuesta->assertRedirect(route('egresado.perfil'));
        $this->assertAuthenticatedAs($usuario);

        $usuario->refresh();
        $this->assertNotNull($usuario->remember_token);

        $respuesta->assertCookie(Auth::guard()->getRecallerName());
    }

    public function test_inicia_sesion_sin_remember_token_cuando_recordar_no_esta_marcado(): void
    {
        $usuario = $this->crearUsuario();

        $respuesta = $this->from('/login')->post('/login', [
            'email' => $usuario->email,
            'password' => 'password',
        ]);

        $respuesta->assertRedirect(route('egresado.perfil'));
        $this->assertAuthenticatedAs($usuario);

        $usuario->refresh();
        $this->assertNull($usuario->remember_token);
    }
}
