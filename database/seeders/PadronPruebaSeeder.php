<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga registros ficticios en el padrón de egresados.
 *
 * SOLO PARA PRUEBAS EN DESARROLLO: permite registrarse en la plataforma
 * (CU-01, RN-01) sin depender del padrón real de Secretaría Académica.
 * No debe ejecutarse en un ambiente de producción.
 */
class PadronPruebaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('padron_egresados')->insertOrIgnore([
            ['dni' => '70000001', 'nombres' => 'Ana Lucía',      'apellidos' => 'Quispe Rojas',      'anio_egreso' => 2019, 'grado' => 'titulado'],
            ['dni' => '70000002', 'nombres' => 'Brayan',         'apellidos' => 'Torres Meza',        'anio_egreso' => 2019, 'grado' => 'bachiller'],
            ['dni' => '70000003', 'nombres' => 'Carla Fiorella',  'apellidos' => 'Huamán Soto',        'anio_egreso' => 2020, 'grado' => 'titulado'],
            ['dni' => '70000004', 'nombres' => 'Diego Alonso',   'apellidos' => 'Ramos Vega',         'anio_egreso' => 2020, 'grado' => 'titulado'],
            ['dni' => '70000005', 'nombres' => 'Estefany',       'apellidos' => 'Cárdenas Lozano',    'anio_egreso' => 2021, 'grado' => 'bachiller'],
            ['dni' => '70000006', 'nombres' => 'Franco Junior',  'apellidos' => 'Palomino Rivera',    'anio_egreso' => 2021, 'grado' => 'titulado'],
            ['dni' => '70000007', 'nombres' => 'Gabriela',       'apellidos' => 'Ninahuanca Espinoza', 'anio_egreso' => 2022, 'grado' => 'titulado'],
            ['dni' => '70000008', 'nombres' => 'Hugo Sebastián', 'apellidos' => 'Camarena Díaz',      'anio_egreso' => 2022, 'grado' => 'bachiller'],
            ['dni' => '70000009', 'nombres' => 'Isabel',         'apellidos' => 'Berrospi Flores',    'anio_egreso' => 2023, 'grado' => 'titulado'],
            ['dni' => '70000010', 'nombres' => 'Junior André',   'apellidos' => 'Salvador Chávez',    'anio_egreso' => 2024, 'grado' => 'bachiller'],
        ]);
    }
}
