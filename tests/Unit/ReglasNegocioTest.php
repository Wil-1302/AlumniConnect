<?php

namespace Tests\Unit;

use App\Domain\Catalogos\Models\PadronEgresado;
use App\Domain\Egresados\Models\Egresado;
use App\Domain\Egresados\Services\RegistroEgresadoService;
use App\Domain\Encuestas\Models\Encuesta;
use App\Domain\Encuestas\Models\OpcionPregunta;
use App\Domain\Encuestas\Models\Pregunta;
use App\Domain\Encuestas\Services\EncuestaService;
use App\Domain\Ofertas\Models\OfertaLaboral;
use App\Domain\Ofertas\Services\OfertaService;
use App\Domain\Seguridad\Models\Usuario;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de las reglas de negocio RN-01 a RN-10.
 *
 * El plan de calidad (E-06) exige que cada regla cuente con verificación.
 * Requieren base de datos real (padrón, catálogos, etc.), así que
 * extienden Tests\TestCase con RefreshDatabase en vez de PHPUnit puro.
 * Cada prueba siembra únicamente los datos que necesita.
 */
class ReglasNegocioTest extends TestCase
{
    use RefreshDatabase;

    /** RN-01: solo puede registrarse quien figura en el padrón institucional. */
    public function test_no_permite_registrar_dni_fuera_del_padron(): void
    {
        $servicio = app(RegistroEgresadoService::class);

        $this->expectException(ReglaNegocioException::class);

        try {
            $servicio->registrar([
                'dni'            => '99999999',
                'email'          => 'nadie@example.test',
                'password'       => 'contrasena-larga',
                'consentimiento' => true,
            ]);
        } catch (ReglaNegocioException $e) {
            $this->assertSame('RN-01', $e->regla);

            throw $e;
        }
    }

    /** RN-02: un documento solo puede estar asociado a una cuenta. */
    public function test_no_permite_dos_cuentas_con_el_mismo_dni(): void
    {
        PadronEgresado::create([
            'dni' => '70050001', 'nombres' => 'Prueba', 'apellidos' => 'RN-02',
            'anio_egreso' => 2021, 'grado' => 'titulado',
        ]);

        $servicio = app(RegistroEgresadoService::class);

        $servicio->registrar([
            'dni'            => '70050001',
            'email'          => 'primera-cuenta@example.test',
            'password'       => 'contrasena-larga',
            'consentimiento' => true,
        ]);

        $this->expectException(ReglaNegocioException::class);

        try {
            $servicio->registrar([
                'dni'            => '70050001',
                'email'          => 'segunda-cuenta@example.test',
                'password'       => 'contrasena-larga',
                'consentimiento' => true,
            ]);
        } catch (ReglaNegocioException $e) {
            $this->assertSame('RN-02', $e->regla);

            throw $e;
        }
    }

    /** RN-04: no se admite publicar, ni queda vigente, una oferta ya vencida. */
    public function test_oferta_vencida_no_esta_vigente_ni_se_puede_publicar(): void
    {
        $administrador = Usuario::create([
            'email' => 'admin-rn04@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);

        $vencida = OfertaLaboral::create([
            'titulo' => 'Oferta vencida', 'empresa' => 'Empresa X',
            'descripcion' => 'Prueba', 'modalidad' => 'remoto',
            'fecha_publicacion' => '2020-01-01', 'fecha_cierre' => '2020-02-01',
            'activa' => true, 'creada_por' => $administrador->id,
        ]);

        $vigente = OfertaLaboral::create([
            'titulo' => 'Oferta vigente', 'empresa' => 'Empresa Y',
            'descripcion' => 'Prueba', 'modalidad' => 'remoto',
            'fecha_publicacion' => now()->toDateString(), 'fecha_cierre' => now()->addDays(10)->toDateString(),
            'activa' => true, 'creada_por' => $administrador->id,
        ]);

        $idsVigentes = OfertaLaboral::vigentes()->pluck('id');

        $this->assertFalse($idsVigentes->contains($vencida->id));
        $this->assertTrue($idsVigentes->contains($vigente->id));

        $servicio = app(OfertaService::class);

        $this->expectException(ReglaNegocioException::class);

        try {
            $servicio->publicar([
                'titulo' => 'Otra oferta vencida', 'empresa' => 'Empresa Z',
                'descripcion' => 'Prueba', 'modalidad' => 'remoto',
                'fecha_cierre' => now()->subDay()->toDateString(),
            ], $administrador->id);
        } catch (ReglaNegocioException $e) {
            $this->assertSame('RN-04', $e->regla);

            throw $e;
        }
    }

    /** RN-06: un egresado responde cada encuesta una sola vez. */
    public function test_no_permite_responder_dos_veces_la_misma_encuesta(): void
    {
        $administrador = Usuario::create([
            'email' => 'admin-rn06@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
        $egresado = $this->crearEgresado();
        $encuesta = Encuesta::create([
            'creada_por' => $administrador->id, 'titulo' => 'Encuesta RN-06',
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDay()->toDateString(), 'activa' => true,
        ]);
        $pregunta = Pregunta::create([
            'encuesta_id' => $encuesta->id, 'enunciado' => '¿Trabajas actualmente?',
            'tipo' => Pregunta::TIPO_OPCION_MULTIPLE, 'orden' => 1,
        ]);
        $opcion = OpcionPregunta::create([
            'pregunta_id' => $pregunta->id, 'texto' => 'Sí', 'orden' => 1,
        ]);

        $servicio = app(EncuestaService::class);
        $respuestas = [$pregunta->id => ['opcion_id' => $opcion->id]];

        $servicio->responder($encuesta, $egresado, $respuestas);

        $this->expectException(ReglaNegocioException::class);

        try {
            $servicio->responder($encuesta, $egresado, $respuestas);
        } catch (ReglaNegocioException $e) {
            $this->assertSame('RN-06', $e->regla);

            throw $e;
        }
    }

    /** RN-07: el directorio administrativo no expone correo ni teléfono. */
    public function test_directorio_no_expone_datos_de_contacto(): void
    {
        $usuario = Usuario::create([
            'email' => 'contacto-privado@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);
        Egresado::create([
            'usuario_id' => $usuario->id, 'dni' => '70050002',
            'nombres' => 'Datos', 'apellidos' => 'Privados', 'anio_egreso' => 2022,
            'grado' => 'titulado', 'telefono' => '999888777',
            'perfil_visible' => true, 'consentimiento_en' => now(),
        ]);

        $admin = Usuario::create([
            'email' => 'admin-rn07@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);

        $respuesta = $this->actingAs($admin)->get('/admin/directorio');

        $respuesta->assertOk();
        $respuesta->assertDontSee('contacto-privado@example.test');
        $respuesta->assertDontSee('999888777');
        // El nombre sí debe mostrarse: confirma que la prueba de verdad
        // renderizó la fila de este egresado y no una tabla vacía.
        $respuesta->assertSee('Privados');
    }

    /** RN-08: sin aceptación expresa no se crea la cuenta. */
    public function test_no_permite_registro_sin_consentimiento(): void
    {
        $servicio = app(RegistroEgresadoService::class);

        $this->expectException(ReglaNegocioException::class);

        try {
            $servicio->registrar([
                'dni'            => '70050003',
                'email'          => 'sin-consentimiento@example.test',
                'password'       => 'contrasena-larga',
                'consentimiento' => false,
            ]);
        } catch (ReglaNegocioException $e) {
            $this->assertSame('RN-08', $e->regla);

            throw $e;
        }
    }

    private function crearEgresado(array $datos = []): Egresado
    {
        $usuario = Usuario::create([
            'email'         => $datos['email'] ?? 'egresado-' . uniqid() . '@example.test',
            'password_hash' => bcrypt('contrasena'),
            'rol'           => Usuario::ROL_EGRESADO,
            'activo'        => true,
        ]);

        return Egresado::create([
            'usuario_id'        => $usuario->id,
            'dni'               => $datos['dni'] ?? (string) random_int(70060000, 70069999),
            'nombres'           => $datos['nombres'] ?? 'Egresado',
            'apellidos'         => $datos['apellidos'] ?? 'De Prueba',
            'anio_egreso'       => $datos['anio_egreso'] ?? 2023,
            'grado'             => $datos['grado'] ?? 'titulado',
            'perfil_visible'    => true,
            'consentimiento_en' => now(),
        ]);
    }
}
