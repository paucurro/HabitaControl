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
        Schema::table('comunidades', function (Blueprint $table) {
            $table->string('modelo_impresion', 200)->nullable()->after('copias_informe');
            $table->unsignedSmallInteger('ano_construccion')->nullable()->after('penalizacion');
            $table->text('iee')->nullable()->after('ano_construccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropColumn(['modelo_impresion', 'ano_construccion', 'iee']);
        });
    }
};
