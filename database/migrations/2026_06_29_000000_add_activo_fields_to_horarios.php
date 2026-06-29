<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->boolean('activo_sistema')->default(true)->after('intervalo');
            $table->boolean('activo_portal')->default(false)->after('activo_sistema');
        });
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn(['activo_sistema', 'activo_portal']);
        });
    }
};
