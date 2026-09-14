<?php

use App\Http\Controllers\Admin\DirectorioController;
use App\Http\Controllers\Admin\OfertaAdminController;
use App\Http\Controllers\Admin\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas administrativas
|--------------------------------------------------------------------------
| Protegidas por el middleware 'rol:administrador,admin_principal'.
*/

Route::middleware(['auth', 'rol:administrador,admin_principal'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DirectorioController::class, 'tablero'])->name('tablero');
        Route::get('/directorio', [DirectorioController::class, 'index'])->name('directorio');

        Route::get('/ofertas', [OfertaAdminController::class, 'index'])->name('ofertas.index');
        Route::get('/ofertas/crear', [OfertaAdminController::class, 'crear'])->name('ofertas.crear');
        Route::post('/ofertas', [OfertaAdminController::class, 'guardar'])->name('ofertas.guardar');
        Route::patch('/ofertas/{id}/desactivar', [OfertaAdminController::class, 'desactivar'])
             ->name('ofertas.desactivar');

        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes');
    });
