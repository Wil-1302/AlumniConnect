<?php

namespace App\Http\Controllers\Egresado;

use App\Domain\Egresados\Requests\RegistrarEgresadoRequest;
use App\Domain\Egresados\Services\RegistroEgresadoService;
use App\Http\Controllers\Controller;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Capa de presentación: no contiene reglas de negocio ni consultas.
 * Solo recibe, delega y responde (E-08 numeral 2.2).
 */
class RegistroController extends Controller
{
    public function __construct(
        private readonly RegistroEgresadoService $registro,
    ) {
    }

    public function mostrar(): View
    {
        return view('egresado.registro');
    }

    public function guardar(RegistrarEgresadoRequest $request): RedirectResponse
    {
        try {
            $this->registro->registrar($request->validated());
        } catch (ReglaNegocioException $e) {
            return back()->withInput()->withErrors(['dni' => $e->getMessage()]);
        }

        return redirect()
            ->route('login')
            ->with('exito', 'Su cuenta fue creada. Ya puede iniciar sesión.');
    }
}
