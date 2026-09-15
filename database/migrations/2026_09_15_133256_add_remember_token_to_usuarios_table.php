<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Al reemplazar la tabla `users` de Laravel por `usuarios` se perdió la
 * columna que el framework necesita para "recordar sesión" (Auth::attempt
 * con $remember = true).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->rememberToken()->after('password_hash');
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
