<?php

namespace Tests\Feature;

use App\Domain\Catalogos\Models\PadronEgresado;
use App\Domain\Egresados\Models\Egresado;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CU-01: registro de un egresado. */
class RegistroEgresadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_el_formulario_de_registro(): void
    {
        $this->get('/registro')->assertOk()->assertSee('Crea tu cuenta');
    }

    public function test_registra_un_egresado_que_figura_en_el_padron(): void
    {
        PadronEgresado::create([
            'dni' => '70070001', 'nombres' => 'Rosa', 'apellidos' => 'Fernández',
            'anio_egreso' => 2020, 'grado' => 'titulado',
        ]);

        $respuesta = $this->post('/registro', [
            'dni'                   => '70070001',
            'email'                 => 'rosa.fernandez@example.test',
            'password'              => 'contrasena-larga',
            'password_confirmation' => 'contrasena-larga',
            'consentimiento'        => '1',
        ]);

        $respuesta->assertRedirect(route('login'));
        $respuesta->assertSessionHas('exito');

        $this->assertDatabaseHas('usuarios', ['email' => 'rosa.fernandez@example.test']);
        $this->assertDatabaseHas('egresados', ['dni' => '70070001', 'nombres' => 'Rosa']);

        $usuario = Usuario::where('email', 'rosa.fernandez@example.test')->first();
        $this->assertTrue(Egresado::where('usuario_id', $usuario->id)->exists());
    }

    public function test_rechaza_el_registro_si_el_dni_no_esta_en_el_padron(): void
    {
        $respuesta = $this->from('/registro')->post('/registro', [
            'dni'                   => '70070002',
            'email'                 => 'nadie@example.test',
            'password'              => 'contrasena-larga',
            'password_confirmation' => 'contrasena-larga',
            'consentimiento'        => '1',
        ]);

        $respuesta->assertRedirect('/registro');
        $respuesta->assertSessionHasErrors('dni');
        $this->assertDatabaseMissing('usuarios', ['email' => 'nadie@example.test']);
    }
}
