<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_excluidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('users')->cascadeOnDelete();
            $table->date('fecha');
            $table->boolean('todo_el_dia')->default(true);
            $table->time('desde')->nullable();
            $table->time('hasta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_excluidos');
    }
};
