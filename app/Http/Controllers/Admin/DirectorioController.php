<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalogos\Repositories\CatalogoRepository;
use App\Domain\Egresados\Repositories\EgresadoRepository;
use App\Domain\Reportes\Services\ReporteEgresadosService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DirectorioController extends Controller
{
    public function __construct(
        private readonly EgresadoRepository $egresados,
        private readonly ReporteEgresadosService $reportes,
        private readonly CatalogoRepository $catalogos,
    ) {
    }

    /** RF-14: tablero de indicadores de empleabilidad. */
    public function tablero(): View
    {
        return view('admin.tablero', ['indicadores' => $this->reportes->indicadores()]);
    }

    /** RF-15: directorio con filtros combinables. */
    public function index(Request $request): View
    {
        $filtros = $request->only(['anio_egreso', 'situacion_id', 'rubro_id']);

        return view('admin.directorio', [
            'egresados'   => $this->egresados->directorio($filtros),
            'situaciones' => $this->catalogos->situaciones(),
            'rubros'      => $this->catalogos->rubros(),
            'filtros'     => $filtros,
        ]);
    }
}
