<?php

namespace App\Http\Controllers\Egresado;

use App\Domain\Encuestas\Repositories\EncuestaRepository;
use App\Domain\Encuestas\Requests\ResponderEncuestaRequest;
use App\Domain\Encuestas\Services\EncuestaService;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Capa de presentación: no contiene reglas de negocio ni consultas.
 * CU-04 (responder encuesta) y RN-06 (una sola respuesta) viven en
 * EncuestaService, ya existente; este controlador no los reimplementa.
 */
class EncuestaController extends Controller
{
    public function __construct(
        private readonly EncuestaService $encuestas,
        private readonly EncuestaRepository $repositorio,
    ) {
    }

    public function index(Request $request): View
    {
        return view('egresado.encuestas.index', [
            'encuestas' => $this->encuestas->pendientesPara($request->user()->egresado),
        ]);
    }

    public function mostrar(Request $request, int $id): View|RedirectResponse
    {
        $encuesta = $this->repositorio->porId($id);

        if ($encuesta === null) {
            abort(404);
        }

        $pendiente = $this->encuestas->pendientesPara($request->user()->egresado)->contains('id', $encuesta->id);

        if (! $pendiente) {
            return redirect()
                ->route('egresado.encuestas.index')
                ->with('error', 'Esta encuesta ya no está disponible para usted.');
        }

        return view('egresado.encuestas.responder', ['encuesta' => $encuesta]);
    }

    public function guardar(int $id, ResponderEncuestaRequest $request): RedirectResponse
    {
        $encuesta = $this->repositorio->porId($id);

        if ($encuesta === null) {
            abort(404);
        }

        try {
            $this->encuestas->responder(
                $encuesta,
                $request->user()->egresado,
                $request->validated()['respuestas'] ?? []
            );
        } catch (ReglaNegocioException $e) {
            return back()->withErrors(['respuestas' => $e->getMessage()]);
        }

        return redirect()
            ->route('egresado.encuestas.index')
            ->with('exito', '¡Gracias! Su respuesta fue registrada.');
    }
}
