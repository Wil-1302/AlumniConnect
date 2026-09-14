<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Carga los catálogos base del sistema.
 *
 * Los catálogos son datos de referencia, no de negocio: se cargan una vez
 * y se mantienen desde la base de datos sin modificar el código.
 */
class CatalogosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('situaciones_laborales')->insertOrIgnore([
            ['nombre' => 'Laborando en el rubro de la carrera',     'cuenta_como_empleo' => true],
            ['nombre' => 'Laborando fuera del rubro de la carrera', 'cuenta_como_empleo' => true],
            ['nombre' => 'Emprendimiento propio',                   'cuenta_como_empleo' => true],
            ['nombre' => 'Estudios de posgrado a tiempo completo',  'cuenta_como_empleo' => false],
            ['nombre' => 'Sin empleo',                              'cuenta_como_empleo' => false],
        ]);

        $rubros = [
            'Desarrollo de software', 'Infraestructura y redes',
            'Seguridad de la información', 'Datos e inteligencia artificial',
            'Soporte técnico', 'Minería', 'Sector público', 'Educación',
            'Comercio y servicios', 'Otro',
        ];

        DB::table('rubros')->insertOrIgnore(
            array_map(fn (string $nombre) => ['nombre' => $nombre], $rubros)
        );
    }
}
