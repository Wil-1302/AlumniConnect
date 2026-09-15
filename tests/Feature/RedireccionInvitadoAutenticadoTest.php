<?php

namespace Tests\Feature;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Un usuario ya autenticado que visita /login (ruta 'guest') no debe caer
 * en la portada pública ('/'), sino en la zona que le corresponde por su
 * rol. Antes de este fix, RedirectIfAuthenticated::defaultRedirectUri()
 * caía a '/' porque la app no registraba una ruta 'home' ni 'dashboard'.
 */
class RedireccionInvitadoAutenticadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_administrador_autenticado_es_enviado_a_su_tablero_al_visitar_login(): void
    {
        $admin = Usuario::create([
            'email' => 'admin@example.test',
            'password_hash' => Hash::make('password'),
            'rol' => Usuario::ROL_ADMIN_PRINCIPAL,
            'activo' => true,
        ]);

        $this->actingAs($admin)
            ->get('/login')
            ->assertRedirect(route('admin.tablero'));
    }

    public function test_un_egresado_autenticado_es_enviado_a_su_perfil_al_visitar_login(): void
    {
        $egresado = Usuario::create([
            'email' => 'egresado@example.test',
            'password_hash' => Hash::make('password'),
            'rol' => Usuario::ROL_EGRESADO,
            'activo' => true,
        ]);

        $this->actingAs($egresado)
            ->get('/login')
            ->assertRedirect(route('egresado.perfil'));
    }
}
