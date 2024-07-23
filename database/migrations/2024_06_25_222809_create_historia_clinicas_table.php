<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('historias_clinicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->onDelete('cascade');
            $table->text('motivo')->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('estudios')->nullable();
            $table->text('tratamiento')->nullable();
            $table->text('medicamentos')->nullable();
            $table->text('examen_fisico')->nullable();
            $table->text('resultados')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historias_clinicas');
    }
};
