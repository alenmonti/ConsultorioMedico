<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('portal_dias_excluidos');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('portal_dias_excluidos')->nullable()->after('portal_dias_anticipacion');
        });
    }
};
