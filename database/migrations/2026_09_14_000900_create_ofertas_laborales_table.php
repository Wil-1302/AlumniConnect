<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ofertas_laborales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('rubro_id')->nullable();
            $table->unsignedInteger('creada_por');
            $table->string('titulo', 150);
            $table->string('empresa', 150);
            $table->text('descripcion');
            $table->text('requisitos')->nullable();
            $table->enum('modalidad', ['presencial', 'remoto', 'hibrido']);
            $table->date('fecha_publicacion');
            $table->date('fecha_cierre')->comment('RN-04: obligatoria');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index(['activa', 'fecha_cierre'], 'ix_ofertas_vigencia');
            $table->foreign('rubro_id')->references('id')->on('rubros')
                  ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('creada_por')->references('id')->on('usuarios')
                  ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ofertas_laborales');
    }
};
