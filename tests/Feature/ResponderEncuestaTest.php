<?php

namespace Tests\Feature;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Encuestas\Models\Encuesta;
use App\Domain\Encuestas\Models\OpcionPregunta;
use App\Domain\Encuestas\Models\Pregunta;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** CU-04: un egresado responde una encuesta vigente. */
class ResponderEncuestaTest extends TestCase
{
    use RefreshDatabase;

    private function crearEgresado(): Egresado
    {
        $usuario = Usuario::create([
            'email' => 'encuesta@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);

        return Egresado::create([
            'usuario_id' => $usuario->id, 'dni' => '70070020',
            'nombres' => 'Vania', 'apellidos' => 'Solano', 'anio_egreso' => 2018,
            'grado' => 'titulado', 'perfil_visible' => true, 'consentimiento_en' => now(),
        ]);
    }

    private function crearEncuestaConDosPreguntas(): array
    {
        $admin = Usuario::create([
            'email' => 'admin-cu04-' . uniqid() . '@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);

        $encuesta = Encuesta::create([
            'creada_por' => $admin->id, 'titulo' => 'Encuesta de seguimiento',
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDays(10)->toDateString(), 'activa' => true,
        ]);

        $preguntaOpcion = Pregunta::create([
            'encuesta_id' => $encuesta->id, 'enunciado' => '¿En qué rubro trabajas?',
            'tipo' => Pregunta::TIPO_OPCION_MULTIPLE, 'orden' => 1,
        ]);
        $opcion = OpcionPregunta::create([
            'pregunta_id' => $preguntaOpcion->id, 'texto' => 'Tecnología', 'orden' => 1,
        ]);
        OpcionPregunta::create(['pregunta_id' => $preguntaOpcion->id, 'texto' => 'Otro', 'orden' => 2]);

        $preguntaEscala = Pregunta::create([
            'encuesta_id' => $encuesta->id, 'enunciado' => '¿Qué tan satisfecho estás?',
            'tipo' => Pregunta::TIPO_ESCALA, 'orden' => 2,
        ]);

        return [$encuesta, $preguntaOpcion, $opcion, $preguntaEscala];
    }

    public function test_ve_la_encuesta_pendiente_y_el_formulario(): void
    {
        $egresado = $this->crearEgresado();
        [$encuesta] = $this->crearEncuestaConDosPreguntas();

        $this->actingAs($egresado->usuario)->get(route('egresado.encuestas.index'))
            ->assertOk()->assertSee($encuesta->titulo);

        $this->actingAs($egresado->usuario)->get(route('egresado.encuestas.responder', $encuesta->id))
            ->assertOk()->assertSee('¿En qué rubro trabajas?')->assertSee('¿Qué tan satisfecho estás?');
    }

    public function test_responde_la_encuesta_correctamente(): void
    {
        $egresado = $this->crearEgresado();
        [$encuesta, $preguntaOpcion, $opcion, $preguntaEscala] = $this->crearEncuestaConDosPreguntas();

        $respuesta = $this->actingAs($egresado->usuario)->post(route('egresado.encuestas.responder', $encuesta->id), [
            'respuestas' => [
                $preguntaOpcion->id => ['opcion_id' => $opcion->id],
                $preguntaEscala->id => ['valor_escala' => 4],
            ],
        ]);

        $respuesta->assertRedirect(route('egresado.encuestas.index'));
        $respuesta->assertSessionHas('exito');
        $this->assertDatabaseHas('respuestas_encuesta', [
            'encuesta_id' => $encuesta->id, 'egresado_id' => $egresado->id,
        ]);

        // RN-06: ya no debe seguir apareciendo como pendiente.
        $this->actingAs($egresado->usuario)->get(route('egresado.encuestas.index'))
            ->assertOk()->assertDontSee($encuesta->titulo);
    }

    public function test_rechaza_una_respuesta_de_pregunta_ajena_a_la_encuesta(): void
    {
        // Verifica la Sección A: la validación de pertenencia vive en
        // EncuestaService, no en el Request, y sigue funcionando ante un
        // envío manipulado con un id de pregunta que no es de esta encuesta.
        $egresado = $this->crearEgresado();
        [$encuesta, $preguntaOpcion, $opcion, $preguntaEscala] = $this->crearEncuestaConDosPreguntas();
        [$otraEncuesta, $otraPregunta] = $this->crearEncuestaConDosPreguntas();

        $respuesta = $this->actingAs($egresado->usuario)
            ->from(route('egresado.encuestas.responder', $encuesta->id))
            ->post(route('egresado.encuestas.responder', $encuesta->id), [
                'respuestas' => [
                    $preguntaOpcion->id => ['opcion_id' => $opcion->id],
                    $preguntaEscala->id => ['valor_escala' => 4],
                    $otraPregunta->id   => ['opcion_id' => 1],
                ],
            ]);

        $respuesta->assertRedirect(route('egresado.encuestas.responder', $encuesta->id));
        $respuesta->assertSessionHasErrors('respuestas');
        $this->assertDatabaseMissing('respuestas_encuesta', [
            'encuesta_id' => $encuesta->id, 'egresado_id' => $egresado->id,
        ]);
    }

    public function test_no_permite_responder_dos_veces_via_http(): void
    {
        $egresado = $this->crearEgresado();
        [$encuesta, $preguntaOpcion, $opcion, $preguntaEscala] = $this->crearEncuestaConDosPreguntas();

        $datos = [
            'respuestas' => [
                $preguntaOpcion->id => ['opcion_id' => $opcion->id],
                $preguntaEscala->id => ['valor_escala' => 3],
            ],
        ];

        $this->actingAs($egresado->usuario)->post(route('egresado.encuestas.responder', $encuesta->id), $datos);

        $segundoIntento = $this->actingAs($egresado->usuario)
            ->post(route('egresado.encuestas.responder', $encuesta->id), $datos);

        $segundoIntento->assertSessionHasErrors('respuestas');
        $this->assertSame(1, DB::table('respuestas_encuesta')
            ->where('encuesta_id', $encuesta->id)->where('egresado_id', $egresado->id)->count());
    }
}
