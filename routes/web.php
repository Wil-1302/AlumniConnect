<?php

use App\Http\Controllers\Egresado\RegistroController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
| Accesibles sin autenticación. Las rutas protegidas se declaran en los
| archivos egresado.php y admin.php, cada uno con su propio middleware.
*/

Route::view('/', 'publico.inicio')->name('inicio');
Route::view('/politica-privacidad', 'publico.politica')->name('politica');

Route::get('/registro', [RegistroController::class, 'mostrar'])->name('registro');
Route::post('/registro', [RegistroController::class, 'guardar']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'mostrar'])->name('login');
    Route::post('/login', [LoginController::class, 'iniciarSesion']);
});

Route::post('/logout', [LoginController::class, 'cerrarSesion'])
    ->middleware('auth')
    ->name('logout');

require __DIR__ . '/egresado.php';
require __DIR__ . '/admin.php';
