<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Encuestas\Repositories\EncuestaRepository;
use App\Domain\Encuestas\Requests\GuardarEncuestaRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EncuestaAdminController extends Controller
{
    public function __construct(
        private readonly EncuestaRepository $encuestas,
    ) {
    }

    /** RF-19: listado administrativo de encuestas. */
    public function index(): View
    {
        return view('admin.encuestas.index', ['encuestas' => $this->encuestas->todas()]);
    }

    public function crear(): View
    {
        return view('admin.encuestas.formulario');
    }

    public function guardar(GuardarEncuestaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $this->encuestas->crear(
            [
                'creada_por'   => $request->user()->id,
                'titulo'       => $datos['titulo'],
                'descripcion'  => $datos['descripcion'] ?? null,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin'    => $datos['fecha_fin'],
                'activa'       => true,
            ],
            $datos['preguntas']
        );

        return redirect()->route('admin.encuestas.index')->with('exito', 'La encuesta fue creada.');
    }

    public function activar(int $id): RedirectResponse
    {
        $this->encuestas->activar($id);

        return back()->with('exito', 'La encuesta fue activada.');
    }

    public function desactivar(int $id): RedirectResponse
    {
        $this->encuestas->desactivar($id);

        return back()->with('exito', 'La encuesta fue desactivada.');
    }

    /** RF-20: resultados consolidados por pregunta. */
    public function resultados(int $id): View
    {
        $encuesta = $this->encuestas->porId($id);

        if ($encuesta === null) {
            abort(404);
        }

        return view('admin.encuestas.resultados', [
            'encuesta'   => $encuesta,
            'resultados' => $this->encuestas->resultados($encuesta),
        ]);
    }
}
