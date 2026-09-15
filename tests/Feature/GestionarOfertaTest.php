<?php

namespace Tests\Feature;

use App\Domain\Ofertas\Models\OfertaLaboral;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ciclo de vida de una oferta ya publicada: desactivar, reactivar, eliminar y ver el detalle. */
class GestionarOfertaTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'email' => 'admin-gestion-'.uniqid().'@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
    }

    private function crearEgresado(): Usuario
    {
        return Usuario::create([
            'email' => 'egresado-gestion-'.uniqid().'@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);
    }

    private function crearOferta(array $atributos = []): OfertaLaboral
    {
        return OfertaLaboral::create(array_merge([
            'creada_por' => $this->crearAdmin()->id,
            'titulo' => 'Backend Developer',
            'empresa' => 'Acme SAC',
            'descripcion' => 'Desarrollo de APIs REST.',
            'requisitos' => 'Laravel y PostgreSQL.',
            'modalidad' => 'remoto',
            'fecha_publicacion' => now()->toDateString(),
            'fecha_cierre' => now()->addDays(20)->toDateString(),
            'activa' => true,
        ], $atributos));
    }

    public function test_un_administrador_reactiva_una_oferta_desactivada(): void
    {
        $oferta = $this->crearOferta(['activa' => false]);

        $respuesta = $this->actingAs($this->crearAdmin())
            ->patch(route('admin.ofertas.activar', $oferta->id));

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseHas('ofertas_laborales', ['id' => $oferta->id, 'activa' => true]);
    }

    public function test_un_administrador_elimina_una_oferta_definitivamente(): void
    {
        $oferta = $this->crearOferta();

        $respuesta = $this->actingAs($this->crearAdmin())
            ->delete(route('admin.ofertas.eliminar', $oferta->id));

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseMissing('ofertas_laborales', ['id' => $oferta->id]);
    }

    public function test_un_egresado_no_puede_reactivar_ni_eliminar_ofertas(): void
    {
        $oferta = $this->crearOferta(['activa' => false]);
        $egresado = $this->crearEgresado();

        $this->actingAs($egresado)->patch(route('admin.ofertas.activar', $oferta->id))->assertForbidden();
        $this->actingAs($egresado)->delete(route('admin.ofertas.eliminar', $oferta->id))->assertForbidden();
    }

    public function test_un_egresado_puede_abrir_el_detalle_de_una_oferta_vigente(): void
    {
        $oferta = $this->crearOferta();

        $this->actingAs($this->crearEgresado())
            ->get(route('egresado.ofertas.detalle', $oferta->id))
            ->assertOk()
            ->assertSee($oferta->titulo)
            ->assertSee($oferta->descripcion)
            ->assertSee($oferta->requisitos);
    }

    public function test_el_detalle_de_una_oferta_inexistente_responde_404(): void
    {
        $this->actingAs($this->crearEgresado())
            ->get(route('egresado.ofertas.detalle', 999))
            ->assertNotFound();
    }
}
