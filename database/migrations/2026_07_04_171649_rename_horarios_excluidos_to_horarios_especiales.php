<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('horarios_excluidos', 'horarios_especiales');
    }

    public function down(): void
    {
        Schema::rename('horarios_especiales', 'horarios_excluidos');
    }
};
