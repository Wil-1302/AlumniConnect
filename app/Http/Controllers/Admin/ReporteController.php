<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reportes\Services\ReporteEgresadosService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function __construct(
        private readonly ReporteEgresadosService $reportes,
    ) {
    }

    public function index(Request $request): View
    {
        $filtros = $request->only(['anio_egreso', 'rubro']);

        return view('admin.reportes', [
            'datos'   => $this->reportes->consolidado($filtros, $request->user()->id),
            'filtros' => $filtros,
        ]);
    }

    // RF-24 y RF-25: la exportación se implementa en las actividades
    // A31 del cronograma, empleando las clases de app/Domain/Reportes/Exporters.
}
