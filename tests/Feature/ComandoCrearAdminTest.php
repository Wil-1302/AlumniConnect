<?php

namespace Tests\Feature;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ComandoCrearAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_un_administrador_por_consola(): void
    {
        $this->artisan('usuario:crear-admin')
            ->expectsQuestion('Correo electrónico del administrador', 'consola@example.test')
            ->expectsQuestion('Contraseña (mínimo 8 caracteres)', 'ClaveSegura123')
            ->expectsQuestion('Confirme la contraseña', 'ClaveSegura123')
            ->assertSuccessful();

        $usuario = Usuario::where('email', 'consola@example.test')->first();
        $this->assertNotNull($usuario);
        $this->assertSame(Usuario::ROL_ADMIN_PRINCIPAL, $usuario->rol);
        $this->assertTrue(Hash::check('ClaveSegura123', $usuario->password_hash));
    }

    public function test_rechaza_correo_ya_existente(): void
    {
        Usuario::create(['email' => 'repetido@example.test', 'password_hash' => 'x', 'rol' => 'egresado', 'activo' => true]);

        $this->artisan('usuario:crear-admin')
            ->expectsQuestion('Correo electrónico del administrador', 'repetido@example.test')
            ->expectsQuestion('Correo electrónico del administrador', 'nuevo@example.test')
            ->expectsQuestion('Contraseña (mínimo 8 caracteres)', 'ClaveSegura123')
            ->expectsQuestion('Confirme la contraseña', 'ClaveSegura123')
            ->assertSuccessful();
    }

    public function test_rechaza_contrasena_corta(): void
    {
        $this->artisan('usuario:crear-admin')
            ->expectsQuestion('Correo electrónico del administrador', 'clave-corta@example.test')
            ->expectsQuestion('Contraseña (mínimo 8 caracteres)', '123')
            ->expectsQuestion('Confirme la contraseña', '123')
            ->expectsQuestion('Contraseña (mínimo 8 caracteres)', 'ClaveSegura123')
            ->expectsQuestion('Confirme la contraseña', 'ClaveSegura123')
            ->assertSuccessful();
    }
}
