<?php

namespace App\Domain\Reportes\Exporters;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * RF-24: reporte consolidado de egresados a formato .xlsx.
 *
 * Recibe los datos ya consultados (ReporteEgresadosService::consolidado(),
 * que a su vez consulta la vista v_situacion_actual_egresado): no repite
 * ni reinterpreta esa consulta, solo la traduce a filas de hoja de cálculo.
 */
class ExcelExporter implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private readonly Collection $datos,
    ) {
    }

    public function collection(): Collection
    {
        return $this->datos;
    }

    public function headings(): array
    {
        return [
            'Nombres', 'Apellidos', 'Promoción', 'Grado', 'Situación',
            'Rubro', 'Empresa', 'Cargo', 'Última actualización',
        ];
    }

    /** @param  object  $fila  Fila de la vista v_situacion_actual_egresado */
    public function map($fila): array
    {
        return [
            $fila->nombres,
            $fila->apellidos,
            $fila->anio_egreso,
            $fila->grado === 'titulado' ? 'Titulado(a)' : 'Bachiller',
            $fila->situacion ?? 'No registrada',
            $fila->rubro ?? '—',
            $fila->empresa ?? '—',
            $fila->cargo ?? '—',
            $fila->actualizado_en ? Carbon::parse($fila->actualizado_en)->format('d/m/Y') : '—',
        ];
    }

    public function title(): string
    {
        return 'Reporte de egresados';
    }
}
