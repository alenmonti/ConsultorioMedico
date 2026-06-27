<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->string('origen')->default('sistema')->after('notas');
            $table->foreignId('paciente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn('origen');
            $table->foreignId('paciente_id')->nullable(false)->change();
        });
    }
};
