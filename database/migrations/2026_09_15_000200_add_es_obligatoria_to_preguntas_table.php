<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hasta ahora EncuestaService exigía responder todas las preguntas sin
 * excepción. Esta columna permite marcar una pregunta como opcional;
 * por defecto true para no cambiar el comportamiento de las encuestas
 * ya creadas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->boolean('es_obligatoria')->default(true)->after('orden');
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn('es_obligatoria');
        });
    }
};
