<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Catalogos\Repositories\CatalogoRepository;
use App\Domain\Ofertas\Repositories\OfertaRepository;
use App\Domain\Ofertas\Requests\GuardarOfertaRequest;
use App\Domain\Ofertas\Services\OfertaService;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OfertaAdminController extends Controller
{
    public function __construct(
        private readonly OfertaService $ofertas,
        private readonly OfertaRepository $repositorio,
        private readonly CatalogoRepository $catalogos,
    ) {
    }

    public function index(): View
    {
        return view('admin.ofertas.index', ['ofertas' => $this->repositorio->todas()]);
    }

    public function crear(): View
    {
        return view('admin.ofertas.formulario', ['rubros' => $this->catalogos->rubros()]);
    }

    public function guardar(GuardarOfertaRequest $request): RedirectResponse
    {
        try {
            $this->ofertas->publicar($request->validated(), $request->user()->id);
        } catch (ReglaNegocioException $e) {
            return back()->withInput()->withErrors(['fecha_cierre' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.ofertas.index')
            ->with('exito', 'La oferta fue publicada.');
    }

    public function desactivar(int $id): RedirectResponse
    {
        $this->ofertas->desactivar($id);

        return back()->with('exito', 'La oferta fue desactivada.');
    }
}
