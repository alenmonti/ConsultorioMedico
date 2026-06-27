<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('portal_turnos_activo')->default(false)->after('medico_id');
            $table->string('especialidad')->nullable()->after('portal_turnos_activo');
            $table->text('descripcion')->nullable()->after('especialidad');
            $table->string('foto_portal')->nullable()->after('descripcion');
            $table->string('whatsapp')->nullable()->after('foto_portal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['portal_turnos_activo', 'especialidad', 'descripcion', 'foto_portal', 'whatsapp']);
        });
    }
};
