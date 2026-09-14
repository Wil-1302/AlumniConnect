<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('situaciones_laborales', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nombre', 60)->unique();
            $table->boolean('cuenta_como_empleo')->default(false)
                  ->comment('Determina si suma a la tasa de inserción laboral');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('situaciones_laborales');
    }
};
