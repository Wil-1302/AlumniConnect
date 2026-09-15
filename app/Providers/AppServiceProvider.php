<?php

namespace App\Providers;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurarLimitesDePeticiones();
        $this->configurarRedireccionDeInvitado();
    }

    /**
     * Por defecto, el middleware 'guest' manda a un usuario ya autenticado
     * que visita /login a la ruta 'home' o, si no existe, a '/'. Esta app
     * no tiene ninguna de las dos, así que sin esto cae en la portada
     * pública en vez de en la zona que le corresponde por su rol.
     */
    private function configurarRedireccionDeInvitado(): void
    {
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            /** @var Usuario $usuario */
            $usuario = $request->user();

            return route($usuario->rutaPrincipal());
        });
    }

    /**
     * Protección contra fuerza bruta en /login: máximo 5 intentos por
     * minuto. La clave combina el correo y la IP, no solo la IP, para que
     * nadie bloquee la cuenta de otra persona probando contraseñas desde
     * su propia conexión ni evada el límite alternando de correo.
     */
    private function configurarLimitesDePeticiones(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $clave = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($clave);
        });
    }
}
