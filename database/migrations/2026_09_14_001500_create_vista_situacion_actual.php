<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Vista de apoyo para el tablero de indicadores y los reportes.
 *
 * El cálculo de la situación laboral vigente reside aquí y no se duplica
 * en el código de aplicación (principio de no repetición, E-08 numeral 2.4).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            CREATE OR REPLACE VIEW v_situacion_actual_egresado AS
            SELECT e.id AS egresado_id, e.nombres, e.apellidos, e.anio_egreso,
                   e.grado, s.nombre AS situacion, s.cuenta_como_empleo,
                   r.nombre AS rubro, x.empresa, x.cargo, e.actualizado_en
            FROM egresados e
            LEFT JOIN experiencias_laborales x
                   ON x.egresado_id = e.id AND x.es_actual = 1
            LEFT JOIN situaciones_laborales s ON s.id = x.situacion_id
            LEFT JOIN rubros r ON r.id = x.rubro_id
        ');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_situacion_actual_egresado');
    }
};
