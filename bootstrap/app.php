<?php

use App\Http\Middleware\VerificarRol;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rol' => VerificarRol::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Bloqueo del limitador "login" (RateLimiter::for en AppServiceProvider):
        // en vez de la página 429 genérica, se regresa al formulario con un
        // mensaje legible, igual que cualquier otro error de validación.
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            $segundos = $e->getHeaders()['Retry-After'] ?? null;
            $mensaje = $segundos
                ? "Demasiados intentos de inicio de sesión. Vuelve a intentarlo en {$segundos} segundos."
                : 'Demasiados intentos de inicio de sesión. Vuelve a intentarlo en unos minutos.';

            return back()
                ->withInput($request->except('password'))
                ->withErrors(['email' => $mensaje]);
        });
    })->create();
