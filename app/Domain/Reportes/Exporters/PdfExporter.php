<?php

namespace App\Domain\Reportes\Exporters;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Collection;

/**
 * RF-25: reporte consolidado de egresados a PDF, con encabezado
 * institucional, fecha de generación y usuario que lo emitió.
 *
 * Al igual que ExcelExporter, recibe los datos ya consultados por
 * ReporteEgresadosService::consolidado() y solo los traduce a documento.
 */
class PdfExporter
{
    public function generar(Collection $datos, string $emitidoPor): DomPdf
    {
        return Pdf::loadView('reportes.pdf-consolidado', [
            'datos'      => $datos,
            'generadoEn' => now(),
            'emitidoPor' => $emitidoPor,
        ])->setPaper('a4', 'landscape');
    }
}
