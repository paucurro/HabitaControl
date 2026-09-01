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
        foreach (['diario_apuntes', 'diario_apuntes_especiales', 'diario_obras'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('fecha_calculada')->default(false)->after('fecha');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['diario_apuntes', 'diario_apuntes_especiales', 'diario_obras'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('fecha_calculada');
            });
        }
    }
};
