<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas_encuesta', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('encuesta_id');
            $table->unsignedInteger('egresado_id')->index();
            $table->timestamp('respondida_en')->useCurrent();

            $table->unique(['encuesta_id', 'egresado_id'], 'uq_respuesta_unica');
            $table->foreign('encuesta_id')->references('id')->on('encuestas')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('egresado_id')->references('id')->on('egresados')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_encuesta');
    }
};
