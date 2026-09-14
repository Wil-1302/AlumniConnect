<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opciones_pregunta', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pregunta_id');
            $table->string('texto', 200);
            $table->tinyInteger('valor')->nullable();
            $table->unsignedTinyInteger('orden')->default(1);

            $table->index(['pregunta_id', 'orden']);
            $table->foreign('pregunta_id')->references('id')->on('preguntas')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opciones_pregunta');
    }
};
