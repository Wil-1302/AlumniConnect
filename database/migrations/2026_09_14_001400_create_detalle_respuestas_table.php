<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_respuestas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('respuesta_id');
            $table->unsignedInteger('pregunta_id')->index();
            $table->unsignedInteger('opcion_id')->nullable();
            $table->tinyInteger('valor_escala')->nullable();

            $table->unique(['respuesta_id', 'pregunta_id'], 'uq_detalle_pregunta');
            $table->foreign('respuesta_id')->references('id')->on('respuestas_encuesta')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('pregunta_id')->references('id')->on('preguntas')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('opcion_id')->references('id')->on('opciones_pregunta')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_respuestas');
    }
};
