<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiencias_laborales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('egresado_id');
            $table->unsignedTinyInteger('situacion_id');
            $table->unsignedSmallInteger('rubro_id')->nullable();
            $table->string('empresa', 150)->nullable();
            $table->string('cargo', 120)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('es_actual')->default(true);
            $table->timestamp('registrado_en')->useCurrent();

            $table->index(['egresado_id', 'es_actual'], 'ix_experiencias_actual');
            $table->foreign('egresado_id')->references('id')->on('egresados')
                  ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('situacion_id')->references('id')->on('situaciones_laborales')
                  ->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('rubro_id')->references('id')->on('rubros')
                  ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiencias_laborales');
    }
};
