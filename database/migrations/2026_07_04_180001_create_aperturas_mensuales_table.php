<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aperturas_mensuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('users');
            $table->smallInteger('anio');
            $table->tinyInteger('mes');
            $table->boolean('abierto')->default(true);
            $table->timestamps();

            $table->unique(['medico_id', 'anio', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aperturas_mensuales');
    }
};
