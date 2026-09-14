<?php

namespace App\Http\Controllers\Egresado;

use App\Domain\Catalogos\Repositories\CatalogoRepository;
use App\Domain\Egresados\Services\PerfilService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilController extends Controller
{
    public function __construct(
        private readonly PerfilService $perfil,
        private readonly CatalogoRepository $catalogos,
    ) {
    }

    public function mostrar(Request $request): View
    {
        $egresado = $request->user()->egresado;

        return view('egresado.perfil', [
            'egresado'    => $egresado->load('experiencias.situacion', 'experiencias.rubro'),
            'situaciones' => $this->catalogos->situaciones(),
            'rubros'      => $this->catalogos->rubros(),
        ]);
    }

    public function actualizar(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'telefono'       => ['nullable', 'string', 'max:20'],
            'ciudad'         => ['nullable', 'string', 'max:80'],
            'perfil_visible' => ['boolean'],
        ]);

        $this->perfil->actualizarDatosPersonales($request->user()->egresado, $datos);

        return back()->with('exito', 'Sus datos fueron actualizados.');
    }

    public function actualizarSituacion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'situacion_id' => ['required', 'integer', 'exists:situaciones_laborales,id'],
            'rubro_id'     => ['nullable', 'integer', 'exists:rubros,id'],
            'empresa'      => ['nullable', 'string', 'max:150'],
            'cargo'        => ['nullable', 'string', 'max:120'],
            'fecha_inicio' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        $this->perfil->actualizarSituacionLaboral($request->user()->egresado, $datos);

        return back()->with('exito', 'Su situación laboral fue registrada.');
    }
}
