<?php

namespace Tests\Feature;

use App\Domain\Catalogos\Models\PadronEgresado;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** RF-31: carga masiva del padrón desde Excel o CSV. */
class ImportarPadronTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdminPrincipal(): Usuario
    {
        return Usuario::create([
            'email' => 'principal@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMIN_PRINCIPAL, 'activo' => true,
        ]);
    }

    private function crearAdministrador(): Usuario
    {
        return Usuario::create([
            'email' => 'administrador@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
    }

    private function archivoCsv(string $contenido, string $nombre = 'padron.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }

    public function test_importa_correctamente_un_archivo_valido(): void
    {
        $csv = "dni,nombres,apellidos,anio_egreso,grado\n"
             . "70080001,Carla,Huaman Soto,2020,titulado\n"
             . "70080002,Diego,Ramos Vega,2021,bachiller\n";

        $respuesta = $this->actingAs($this->crearAdminPrincipal())
            ->post(route('admin.padron.importar.guardar'), ['archivo' => $this->archivoCsv($csv)]);

        $respuesta->assertRedirect(route('admin.padron.index'));
        $respuesta->assertSessionHas('exito');

        $this->assertDatabaseHas('padron_egresados', ['dni' => '70080001', 'apellidos' => 'Huaman Soto']);
        $this->assertDatabaseHas('padron_egresados', ['dni' => '70080002', 'apellidos' => 'Ramos Vega']);
        $this->assertSame(2, DB::table('padron_egresados')->count());

        // RN: cada importación queda auditada.
        $this->assertDatabaseHas('auditoria_accesos', ['accion' => 'importar_padron', 'entidad' => 'padron_egresados']);
    }

    public function test_rechaza_un_archivo_con_columnas_faltantes(): void
    {
        // Falta la columna "grado".
        $csv = "dni,nombres,apellidos,anio_egreso\n70080003,Estefany,Cardenas Lozano,2021\n";

        $respuesta = $this->actingAs($this->crearAdminPrincipal())
            ->from(route('admin.padron.importar'))
            ->post(route('admin.padron.importar.guardar'), ['archivo' => $this->archivoCsv($csv)]);

        $respuesta->assertRedirect(route('admin.padron.importar'));
        $respuesta->assertSessionHasErrors('archivo');
        $this->assertSame(0, DB::table('padron_egresados')->count());
    }

    public function test_omite_una_fila_con_dni_invalido_sin_detener_la_importacion(): void
    {
        $csv = "dni,nombres,apellidos,anio_egreso,grado\n"
             . "7008-INVALIDO,Franco,Palomino Rivera,2021,titulado\n"
             . "70080005,Gabriela,Ninahuanca Espinoza,2022,titulado\n";

        $respuesta = $this->actingAs($this->crearAdminPrincipal())
            ->post(route('admin.padron.importar.guardar'), ['archivo' => $this->archivoCsv($csv)]);

        $respuesta->assertRedirect(route('admin.padron.index'));
        $this->assertDatabaseHas('padron_egresados', ['dni' => '70080005']);
        $this->assertDatabaseMissing('padron_egresados', ['nombres' => 'Franco']);
        $this->assertSame(1, DB::table('padron_egresados')->count());
    }

    public function test_omite_un_dni_duplicado_sin_sobrescribir(): void
    {
        PadronEgresado::create([
            'dni' => '70080006', 'nombres' => 'Registro Original', 'apellidos' => 'No Tocar',
            'anio_egreso' => 2019, 'grado' => 'titulado',
        ]);

        $csv = "dni,nombres,apellidos,anio_egreso,grado\n"
             . "70080006,Nombre Nuevo,Apellido Nuevo,2023,bachiller\n"
             . "70080007,Hugo,Camarena Diaz,2022,bachiller\n";

        $respuesta = $this->actingAs($this->crearAdminPrincipal())
            ->post(route('admin.padron.importar.guardar'), ['archivo' => $this->archivoCsv($csv)]);

        $respuesta->assertRedirect(route('admin.padron.index'));

        // El registro original sigue intacto: no se sobrescribió.
        $this->assertDatabaseHas('padron_egresados', ['dni' => '70080006', 'nombres' => 'Registro Original']);
        $this->assertDatabaseMissing('padron_egresados', ['nombres' => 'Nombre Nuevo']);
        $this->assertDatabaseHas('padron_egresados', ['dni' => '70080007']);
        $this->assertSame(2, DB::table('padron_egresados')->count());
    }

    public function test_un_administrador_no_puede_importar_el_padron(): void
    {
        $administrador = $this->crearAdministrador();
        $csv = "dni,nombres,apellidos,anio_egreso,grado\n70080008,Junior,Salvador Chavez,2024,bachiller\n";

        $this->actingAs($administrador)->get(route('admin.padron.importar'))->assertForbidden();

        $respuesta = $this->actingAs($administrador)
            ->post(route('admin.padron.importar.guardar'), ['archivo' => $this->archivoCsv($csv)]);

        $respuesta->assertForbidden();
        $this->assertSame(0, DB::table('padron_egresados')->count());

        // Pero sí puede consultar el padrón.
        $this->actingAs($administrador)->get(route('admin.padron.index'))->assertOk();
    }
}
