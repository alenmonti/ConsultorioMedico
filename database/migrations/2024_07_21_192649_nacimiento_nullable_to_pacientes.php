<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('telefono')->nullable()->change();
            $table->string('afiliado')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->change();
            $table->string('telefono')->change();
            $table->string('afiliado')->change();
        });
    }
};
