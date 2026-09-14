<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RF-27: control de acceso por rol.
 *
 * Se aplica a los grupos de rutas, no a controladores individuales, de modo
 * que una ruta nueva quede protegida por el solo hecho de ubicarse en el
 * archivo correcto.
 */
class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if ($usuario === null || ! $usuario->activo) {
            return redirect()->route('login');
        }

        if (! in_array($usuario->rol, $roles, true)) {
            abort(403, 'No cuenta con permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
