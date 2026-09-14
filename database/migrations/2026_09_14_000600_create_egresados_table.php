<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id')->unique();
            $table->char('dni', 8)->unique()->comment('RN-02: un DNI, una sola cuenta');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->smallInteger('anio_egreso')->index();
            $table->enum('grado', ['bachiller', 'titulado']);
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->boolean('perfil_visible')->default(true)->comment('RF-12');
            $table->dateTime('consentimiento_en')->comment('RF-04 / RN-08');
            $table->dateTime('actualizado_en')->nullable();
            $table->timestamps();

            $table->foreign('usuario_id')->references('id')->on('usuarios')
                  ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresados');
    }
};
