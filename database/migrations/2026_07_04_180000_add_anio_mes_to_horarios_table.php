<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->smallInteger('anio')->nullable()->after('medico_id');
            $table->tinyInteger('mes')->nullable()->after('anio');
        });

        \DB::table('horarios')->update([
            'anio' => now()->year,
            'mes' => now()->month,
        ]);
    }

    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn(['anio', 'mes']);
        });
    }
};
