<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El grado (diplomado/maestría/doctorado) no dice en qué especialidad se
 * cursó. Columna nullable: los registros existentes no tienen este dato.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudios_posgrado', function (Blueprint $table) {
            $table->string('nombre_programa', 150)->nullable()->after('grado');
        });
    }

    public function down(): void
    {
        Schema::table('estudios_posgrado', function (Blueprint $table) {
            $table->dropColumn('nombre_programa');
        });
    }
};
