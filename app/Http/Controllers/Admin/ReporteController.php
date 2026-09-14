<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalogos\Repositories\CatalogoRepository;
use App\Domain\Reportes\Exporters\ExcelExporter;
use App\Domain\Reportes\Exporters\PdfExporter;
use App\Domain\Reportes\Services\ReporteEgresadosService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReporteController extends Controller
{
    public function __construct(
        private readonly ReporteEgresadosService $reportes,
        private readonly CatalogoRepository $catalogos,
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->only(['anio_egreso', 'rubro']);

        return view('admin.reportes', [
            'datos'   => $this->reportes->consolidado($filtros, $request->user()->id),
            'filtros' => $filtros,
            'rubros'  => $this->catalogos->rubros(),
        ]);
    }

    /** RF-24: exportación a Excel. consolidado() ya registra la auditoría (RN-09). */
    public function exportarExcel(Request $request): BinaryFileResponse
    {
        $filtros = $request->only(['anio_egreso', 'rubro']);
        $datos = $this->reportes->consolidado($filtros, $request->user()->id);

        return Excel::download(
            new ExcelExporter($datos),
            'reporte-egresados-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /** RF-25: exportación a PDF con encabezado institucional. */
    public function exportarPdf(Request $request, PdfExporter $exportadorPdf): Response
    {
        $filtros = $request->only(['anio_egreso', 'rubro']);
        $datos = $this->reportes->consolidado($filtros, $request->user()->id);

        return $exportadorPdf
            ->generar($datos, $request->user()->email)
            ->download('reporte-egresados-' . now()->format('Y-m-d') . '.pdf');
    }
}
