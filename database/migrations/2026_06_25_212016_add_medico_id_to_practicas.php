<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->foreignId('medico_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('practicas', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'medico_id');
            $table->dropColumn('medico_id');
        });
    }
};
