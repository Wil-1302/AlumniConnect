<?php

namespace Tests\Feature;

use App\Domain\Catalogos\Models\Rubro;
use App\Domain\Catalogos\Models\SituacionLaboral;
use App\Domain\Egresados\Models\Egresado;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CU-02: actualizar datos personales y situación laboral del egresado. */
class ActualizarPerfilTest extends TestCase
{
    use RefreshDatabase;

    private function crearEgresado(): Egresado
    {
        $usuario = Usuario::create([
            'email' => 'perfil@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);

        return Egresado::create([
            'usuario_id' => $usuario->id, 'dni' => '70070010',
            'nombres' => 'Marco', 'apellidos' => 'Aliaga', 'anio_egreso' => 2019,
            'grado' => 'titulado', 'perfil_visible' => true, 'consentimiento_en' => now(),
        ]);
    }

    public function test_el_egresado_ve_su_perfil(): void
    {
        $egresado = $this->crearEgresado();

        $this->actingAs($egresado->usuario)
            ->get(route('egresado.perfil'))
            ->assertOk()
            ->assertSee('Aliaga, Marco');
    }

    public function test_actualiza_los_datos_personales(): void
    {
        $egresado = $this->crearEgresado();

        $respuesta = $this->actingAs($egresado->usuario)->put(route('egresado.perfil.actualizar'), [
            'telefono'       => '987654321',
            'ciudad'         => 'Cerro de Pasco',
            'perfil_visible' => '1',
        ]);

        $respuesta->assertRedirect();
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseHas('egresados', [
            'id' => $egresado->id, 'telefono' => '987654321', 'ciudad' => 'Cerro de Pasco',
        ]);
    }

    public function test_registra_la_situacion_laboral_y_pasa_la_anterior_al_historial(): void
    {
        $egresado = $this->crearEgresado();
        $situacion = SituacionLaboral::create([
            'nombre' => 'Laborando en el rubro de la carrera',
            'cuenta_como_empleo' => true, 'requiere_detalle_laboral' => true,
        ]);
        $rubro = Rubro::create(['nombre' => 'Desarrollo de software']);

        $datos = [
            'situacion_id' => $situacion->id, 'rubro_id' => $rubro->id,
            'empresa' => 'Empresa Uno', 'cargo' => 'Analista', 'fecha_inicio' => '2024-01-15',
        ];

        $this->actingAs($egresado->usuario)->post(route('egresado.situacion.actualizar'), $datos)
            ->assertRedirect()
            ->assertSessionHas('exito');

        $this->assertDatabaseHas('experiencias_laborales', [
            'egresado_id' => $egresado->id, 'empresa' => 'Empresa Uno', 'es_actual' => true,
        ]);

        // Una segunda situación debe dejar la primera como historial (es_actual = false).
        $this->actingAs($egresado->usuario)->post(route('egresado.situacion.actualizar'), [
            'situacion_id' => $situacion->id, 'rubro_id' => $rubro->id,
            'empresa' => 'Empresa Dos', 'cargo' => 'Analista Senior', 'fecha_inicio' => '2025-01-15',
        ]);

        $this->assertDatabaseHas('experiencias_laborales', ['empresa' => 'Empresa Uno', 'es_actual' => false]);
        $this->assertDatabaseHas('experiencias_laborales', ['empresa' => 'Empresa Dos', 'es_actual' => true]);
    }
}
