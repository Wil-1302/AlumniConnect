<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria_accesos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->string('accion', 60);
            $table->string('entidad', 60)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('fecha_hora')->useCurrent()->index();

            $table->foreign('usuario_id')->references('id')->on('usuarios')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria_accesos');
    }
};
