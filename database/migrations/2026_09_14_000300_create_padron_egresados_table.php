<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('padron_egresados', function (Blueprint $table) {
            $table->increments('id');
            $table->char('dni', 8)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->smallInteger('anio_egreso')->index();
            $table->enum('grado', ['bachiller', 'titulado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padron_egresados');
    }
};
