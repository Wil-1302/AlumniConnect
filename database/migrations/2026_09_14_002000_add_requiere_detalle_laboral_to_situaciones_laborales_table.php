<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Antes, la vista de perfil decidía si mostrar empresa/cargo/rubro
 * comparando el NOMBRE literal de la situación laboral, lo que la dejaba
 * rota ante un simple cambio de texto en el catálogo. Esta columna hace
 * explícita esa regla como dato, no como texto (E-08 numeral 2.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('situaciones_laborales', function (Blueprint $table) {
            $table->boolean('requiere_detalle_laboral')->default(true)->after('cuenta_como_empleo')
                  ->comment('Si es false, el formulario de perfil oculta empresa/cargo/rubro');
        });

        DB::table('situaciones_laborales')
            ->whereIn('nombre', ['Sin empleo', 'Estudios de posgrado a tiempo completo'])
            ->update(['requiere_detalle_laboral' => false]);
    }

    public function down(): void
    {
        Schema::table('situaciones_laborales', function (Blueprint $table) {
            $table->dropColumn('requiere_detalle_laboral');
        });
    }
};
