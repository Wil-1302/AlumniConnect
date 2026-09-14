<?php

namespace Tests\Feature;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** CU-06: un administrador consulta el directorio de egresados. */
class DirectorioTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'email' => 'admin-cu06@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
    }

    private function crearEgresado(string $dni, string $apellidos, int $anioEgreso, bool $perfilVisible): Egresado
    {
        $usuario = Usuario::create([
            'email' => "$dni@example.test", 'password_hash' => 'x',
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);

        return Egresado::create([
            'usuario_id' => $usuario->id, 'dni' => $dni, 'nombres' => 'Nombre',
            'apellidos' => $apellidos, 'anio_egreso' => $anioEgreso, 'grado' => 'titulado',
            'perfil_visible' => $perfilVisible, 'consentimiento_en' => now(),
        ]);
    }

    public function test_lista_solo_egresados_con_perfil_visible(): void
    {
        $this->crearEgresado('70070040', 'Visible', 2022, true);
        $this->crearEgresado('70070041', 'Invisible', 2022, false);

        $respuesta = $this->actingAs($this->crearAdmin())->get(route('admin.directorio'));

        $respuesta->assertOk();
        $respuesta->assertSee('Visible');
        $respuesta->assertDontSee('Invisible');
    }

    public function test_filtra_por_promocion(): void
    {
        $this->crearEgresado('70070042', 'Promocion2020', 2020, true);
        $this->crearEgresado('70070043', 'Promocion2023', 2023, true);

        $respuesta = $this->actingAs($this->crearAdmin())
            ->get(route('admin.directorio', ['anio_egreso' => 2020]));

        $respuesta->assertOk();
        $respuesta->assertSee('Promocion2020');
        $respuesta->assertDontSee('Promocion2023');
    }

    public function test_muestra_estado_vacio_sin_resultados(): void
    {
        $respuesta = $this->actingAs($this->crearAdmin())
            ->get(route('admin.directorio', ['anio_egreso' => 1999]));

        $respuesta->assertOk();
        $respuesta->assertSee('No se encontraron egresados');
    }

    public function test_un_egresado_no_puede_ver_el_directorio(): void
    {
        $usuario = Usuario::create([
            'email' => 'egresado-cu06@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);

        $this->actingAs($usuario)->get(route('admin.directorio'))->assertForbidden();
    }
}
