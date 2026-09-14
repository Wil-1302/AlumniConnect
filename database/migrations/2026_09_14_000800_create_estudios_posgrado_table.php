<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudios_posgrado', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('egresado_id')->index();
            $table->enum('grado', ['diplomado', 'maestria', 'doctorado']);
            $table->string('institucion', 150);
            $table->smallInteger('anio_inicio')->nullable();
            $table->smallInteger('anio_fin')->nullable();
            $table->boolean('en_curso')->default(false);

            $table->foreign('egresado_id')->references('id')->on('egresados')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudios_posgrado');
    }
};
