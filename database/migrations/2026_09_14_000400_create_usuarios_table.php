<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255)->comment('RF-28: nunca en texto plano');
            $table->enum('rol', ['egresado', 'administrador', 'admin_principal'])->index();
            $table->boolean('activo')->default(true);
            $table->dateTime('ultimo_acceso')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
