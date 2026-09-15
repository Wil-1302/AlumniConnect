<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalogos\Exports\PlantillaPadronExport;
use App\Domain\Catalogos\Repositories\PadronRepository;
use App\Domain\Catalogos\Requests\ImportarPadronRequest;
use App\Domain\Catalogos\Services\PadronService;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PadronController extends Controller
{
    public function __construct(
        private readonly PadronRepository $padron,
        private readonly PadronService $servicio,
    ) {
    }

    /** RF-31: consulta paginada, disponible para administrador y admin_principal. */
    public function index(Request $request): View
    {
        $busqueda = $request->query('busqueda');

        return view('admin.padron.index', [
            'registros' => $this->padron->paginado($busqueda),
            'busqueda'  => $busqueda,
        ]);
    }

    /** Solo admin_principal (middleware de ruta). */
    public function formularioImportar(): View
    {
        return view('admin.padron.importar');
    }

    public function importar(ImportarPadronRequest $request): RedirectResponse
    {
        try {
            $resumen = $this->servicio->importar($request->file('archivo'), $request->user()->id);
        } catch (ReglaNegocioException $e) {
            return back()->withErrors(['archivo' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.padron.index')
            ->with('exito', "Importación completa: {$resumen->importados} registros nuevos, {$resumen->omitidos} omitidos.")
            ->with('detalle_omitidos', $resumen->detalle_omitidos);
    }

    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(new PlantillaPadronExport(), 'plantilla-padron-egresados.xlsx');
    }
}
