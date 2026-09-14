<?php

namespace App\Http\Controllers;

use App\Domain\Seguridad\Models\Usuario;
use App\Domain\Seguridad\Services\AutenticacionService;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Capa de presentación: no contiene reglas de autenticación ni consultas.
 * Toda la lógica vive en AutenticacionService (E-08 numeral 2.2).
 */
class LoginController extends Controller
{
    public function __construct(
        private readonly AutenticacionService $autenticacion,
    ) {
    }

    public function mostrar(): View
    {
        return view('auth.login');
    }

    public function iniciarSesion(Request $request): RedirectResponse
    {
        $credenciales = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $usuario = $this->autenticacion->autenticar(
                $credenciales['email'],
                $credenciales['password'],
                $request->boolean('recordar'),
            );
        } catch (ReglaNegocioException $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $e->getMessage()]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->rutaSegunRol($usuario));
    }

    public function cerrarSesion(Request $request): RedirectResponse
    {
        $this->autenticacion->cerrarSesion();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('exito', 'Sesión cerrada correctamente.');
    }

    private function rutaSegunRol(Usuario $usuario): string
    {
        return $usuario->esAdministrador()
            ? route('admin.tablero')
            : route('egresado.perfil');
    }
}
