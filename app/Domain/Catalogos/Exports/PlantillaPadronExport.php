<?php

namespace App\Domain\Catalogos\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Plantilla de ejemplo para la importación del padrón (RF-31): mismas
 * columnas, en el mismo orden, que PadronService::resolverColumnas()
 * espera.
 */
class PlantillaPadronExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['70000001', 'Ana Lucía', 'Quispe Rojas', 2020, 'titulado'],
            ['70000002', 'Brayan', 'Torres Meza', 2021, 'bachiller'],
        ];
    }

    public function headings(): array
    {
        return ['dni', 'nombres', 'apellidos', 'anio_egreso', 'grado'];
    }
}
