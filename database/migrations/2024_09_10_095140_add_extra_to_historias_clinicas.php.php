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
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->text('toxicos')->nullable();
            $table->text('quirurgicos')->nullable();
            $table->text('alergias')->nullable();
            $table->text('vacunacion')->nullable();
            $table->text('medicacion')->nullable();
            $table->json('imagenes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('historias_clinicas', function (Blueprint $table) {
            $table->dropColumn('toxicos');
            $table->dropColumn('quirurgicos');
            $table->dropColumn('alergias');
            $table->dropColumn('vacunacion');
            $table->dropColumn('medicacion');
            $table->dropColumn('imagenes');
        });
    }
};
