<?php

namespace App\Http\Controllers\Egresado;

use App\Domain\Catalogos\Repositories\CatalogoRepository;
use App\Domain\Ofertas\Services\OfertaService;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OfertaController extends Controller
{
    public function __construct(
        private readonly OfertaService $ofertas,
        private readonly CatalogoRepository $catalogos,
    ) {}

    public function index(Request $request): View
    {
        $filtros = $request->only(['rubro_id', 'modalidad']);

        return view('egresado.ofertas', [
            'ofertas' => $this->ofertas->listarVigentes($filtros),
            'rubros' => $this->catalogos->rubros(),
            'filtros' => $filtros,
        ]);
    }

    public function mostrar(int $id): View
    {
        try {
            $oferta = $this->ofertas->verDetalle($id);
        } catch (ReglaNegocioException) {
            throw new NotFoundHttpException;
        }

        return view('egresado.oferta-detalle', ['oferta' => $oferta]);
    }
}
