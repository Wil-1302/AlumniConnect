<?php

use App\Http\Controllers\Admin\DirectorioController;
use App\Http\Controllers\Admin\EncuestaAdminController;
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
        Route::get('/reportes/exportar/excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar.excel');
        Route::get('/reportes/exportar/pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.exportar.pdf');

        Route::prefix('encuestas')->name('encuestas.')->group(function () {
            Route::get('/', [EncuestaAdminController::class, 'index'])->name('index');
            Route::get('/crear', [EncuestaAdminController::class, 'crear'])->name('crear');
            Route::post('/', [EncuestaAdminController::class, 'guardar'])->name('guardar');
            Route::patch('/{id}/activar', [EncuestaAdminController::class, 'activar'])->name('activar');
            Route::patch('/{id}/desactivar', [EncuestaAdminController::class, 'desactivar'])->name('desactivar');
            Route::get('/{id}/resultados', [EncuestaAdminController::class, 'resultados'])->name('resultados');
        });
    });
