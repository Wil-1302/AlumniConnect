<?php

use App\Http\Controllers\Egresado\EncuestaController;
use App\Http\Controllers\Egresado\OfertaController;
use App\Http\Controllers\Egresado\PerfilController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas del egresado
|--------------------------------------------------------------------------
| Protegidas por el middleware 'rol:egresado'. Toda ruta nueva de este
| ámbito debe declararse aquí y no en web.php.
*/

Route::middleware(['auth', 'rol:egresado'])
    ->prefix('mi')
    ->name('egresado.')
    ->group(function () {
        Route::get('/perfil', [PerfilController::class, 'mostrar'])->name('perfil');
        Route::put('/perfil', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');
        Route::post('/situacion', [PerfilController::class, 'actualizarSituacion'])
            ->name('situacion.actualizar');

        Route::get('/ofertas', [OfertaController::class, 'index'])->name('ofertas');
        Route::get('/ofertas/{id}', [OfertaController::class, 'mostrar'])->name('ofertas.detalle');

        Route::prefix('encuestas')->name('encuestas.')->group(function () {
            Route::get('/', [EncuestaController::class, 'index'])->name('index');
            Route::get('/{id}/responder', [EncuestaController::class, 'mostrar'])->name('responder');
            Route::post('/{id}/responder', [EncuestaController::class, 'guardar'])->name('guardar');
        });
    });
