<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->timestamp('senia_informada_at')->nullable()->after('notas');
            $table->timestamp('senia_pagada_at')->nullable()->after('senia_informada_at');
            $table->timestamp('recordatorio_enviado_at')->nullable()->after('senia_pagada_at');
            $table->string('turno_token', 64)->nullable()->unique()->after('recordatorio_enviado_at');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropColumn([
                'senia_informada_at',
                'senia_pagada_at',
                'recordatorio_enviado_at',
                'turno_token',
            ]);
        });
    }
};
