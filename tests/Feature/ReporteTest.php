<?php

namespace Tests\Feature;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** CU-05: generar y exportar el reporte consolidado de egresados. */
class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdmin(): Usuario
    {
        return Usuario::create([
            'email' => 'admin-cu05@example.test', 'password_hash' => bcrypt('contrasena'),
            'rol' => Usuario::ROL_ADMINISTRADOR, 'activo' => true,
        ]);
    }

    private function sembrarUnEgresado(): void
    {
        $usuario = Usuario::create([
            'email' => 'reporte@example.test', 'password_hash' => 'x',
            'rol' => Usuario::ROL_EGRESADO, 'activo' => true,
        ]);
        Egresado::create([
            'usuario_id' => $usuario->id, 'dni' => '70070030',
            'nombres' => 'Iván', 'apellidos' => 'Bustamante', 'anio_egreso' => 2021,
            'grado' => 'bachiller', 'perfil_visible' => true, 'consentimiento_en' => now(),
        ]);
    }

    public function test_muestra_la_pagina_de_reportes_con_vista_previa(): void
    {
        $this->sembrarUnEgresado();

        $this->actingAs($this->crearAdmin())->get(route('admin.reportes'))
            ->assertOk()->assertSee('Bustamante');
    }

    public function test_exporta_a_excel_y_queda_registrado_en_auditoria(): void
    {
        $this->sembrarUnEgresado();
        $admin = $this->crearAdmin();

        $antes = DB::table('auditoria_accesos')
            ->where('usuario_id', $admin->id)->where('accion', 'exportar_reporte')->count();

        $respuesta = $this->actingAs($admin)->get(route('admin.reportes.exportar.excel'));

        $respuesta->assertOk();
        $respuesta->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $despues = DB::table('auditoria_accesos')
            ->where('usuario_id', $admin->id)->where('accion', 'exportar_reporte')->count();
        $this->assertSame($antes + 1, $despues);
    }

    public function test_exporta_a_pdf_y_queda_registrado_en_auditoria(): void
    {
        $this->sembrarUnEgresado();
        $admin = $this->crearAdmin();

        $antes = DB::table('auditoria_accesos')
            ->where('usuario_id', $admin->id)->where('accion', 'exportar_reporte')->count();

        $respuesta = $this->actingAs($admin)->get(route('admin.reportes.exportar.pdf'));

        $respuesta->assertOk();
        $respuesta->assertHeader('content-type', 'application/pdf');

        $despues = DB::table('auditoria_accesos')
            ->where('usuario_id', $admin->id)->where('accion', 'exportar_reporte')->count();
        $this->assertSame($antes + 1, $despues);
    }
}
