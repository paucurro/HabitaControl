<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'comunidades', 'bancos', 'proveedores', 'tipos_deposito', 'partes',
        'propietarios', 'parte_propietario', 'tipos_gasto', 'coeficientes', 'cuotas',
        'tipos_obra', 'diario_apuntes', 'diario_apuntes_especiales', 'diario_obras',
        'liquidaciones', 'liquidacion_lineas', 'liquidacion_partes',
        'liquidacion_propietarios', 'liquidacion_parte_propietario',
        'liquidacion_estados', 'liquidacion_envios', 'comunicados',
        'tipos_siniestro', 'siniestros', 'siniestro_seguimientos',
        'incidencias', 'incidencia_seguimientos',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->string('legacy_source', 50)->nullable()->after('legacy_id');
                $table->index(['legacy_source', 'legacy_id'], $tableName.'_legacy_origin_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndex($tableName.'_legacy_origin_idx');
                $table->dropColumn('legacy_source');
            });
        }
    }
};
