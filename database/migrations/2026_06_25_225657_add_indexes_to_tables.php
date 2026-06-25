<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->index('medico_id');
            $table->index('fecha');
            $table->index(['medico_id', 'fecha']);
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->index('medico_id');
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->index('medico_id');
            $table->index(['medico_id', 'dia']);
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropIndex(['medico_id']);
            $table->dropIndex(['fecha']);
            $table->dropIndex(['medico_id', 'fecha']);
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex(['medico_id']);
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->dropIndex(['medico_id']);
            $table->dropIndex(['medico_id', 'dia']);
        });
    }
};
