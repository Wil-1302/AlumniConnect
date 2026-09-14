<?php

namespace Tests\Feature;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CU-03: un administrador publica una oferta laboral. */
class PublicarOfertaTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'email' => 'admin-cu03@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
    }

    public function test_muestra_el_formulario_de_publicacion(): void
    {
        $this->actingAs($this->crearAdmin())
            ->get(route('admin.ofertas.crear'))
            ->assertOk()
            ->assertSee('Publicar nueva oferta laboral');
    }

    public function test_publica_una_oferta_valida(): void
    {
        $admin = $this->crearAdmin();

        $respuesta = $this->actingAs($admin)->post(route('admin.ofertas.guardar'), [
            'titulo'       => 'Backend Developer',
            'empresa'      => 'Acme SAC',
            'descripcion'  => 'Desarrollo de APIs REST.',
            'modalidad'    => 'remoto',
            'fecha_cierre' => now()->addDays(20)->toDateString(),
        ]);

        $respuesta->assertRedirect(route('admin.ofertas.index'));
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseHas('ofertas_laborales', [
            'titulo' => 'Backend Developer', 'creada_por' => $admin->id, 'activa' => true,
        ]);
    }

    /** RN-04, verificada también a nivel HTTP. */
    public function test_rechaza_una_oferta_con_fecha_de_cierre_vencida(): void
    {
        $admin = $this->crearAdmin();

        $respuesta = $this->actingAs($admin)->from(route('admin.ofertas.crear'))->post(route('admin.ofertas.guardar'), [
            'titulo'       => 'Oferta inválida',
            'empresa'      => 'Acme SAC',
            'descripcion'  => 'Prueba.',
            'modalidad'    => 'remoto',
            'fecha_cierre' => now()->subDay()->toDateString(),
        ]);

        $respuesta->assertRedirect(route('admin.ofertas.crear'));
        $respuesta->assertSessionHasErrors('fecha_cierre');
        $this->assertDatabaseMissing('ofertas_laborales', ['titulo' => 'Oferta inválida']);
    }

    public function test_un_egresado_no_puede_publicar_ofertas(): void
    {
        $usuario = Usuario::create([
            'email' => 'egresado-cu03@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);

        $this->actingAs($usuario)->get(route('admin.ofertas.crear'))->assertForbidden();
    }
}
