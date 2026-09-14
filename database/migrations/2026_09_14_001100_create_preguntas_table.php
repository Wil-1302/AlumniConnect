<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('encuesta_id');
            $table->string('enunciado', 300);
            $table->enum('tipo', ['opcion_multiple', 'escala']);
            $table->unsignedTinyInteger('orden')->default(1);

            $table->index(['encuesta_id', 'orden']);
            $table->foreign('encuesta_id')->references('id')->on('encuestas')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
