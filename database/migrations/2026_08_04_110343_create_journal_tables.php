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
        Schema::create('tipos_obra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->string('codigo', 20);
            $table->string('descripcion', 200);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['comunidad_id', 'codigo']);
        });

        Schema::create('diario_apuntes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('tipo_gasto_id')->nullable()->index();
            $table->unsignedBigInteger('banco_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->unsignedBigInteger('proveedor_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->nullable()->index();
            $table->date('fecha')->index();
            $table->string('numero_documento', 50)->nullable()->index();
            $table->string('descripcion', 200);
            $table->decimal('debe', 15, 4)->default(0);
            $table->decimal('haber', 15, 4)->default(0);
            $table->decimal('base_imponible', 15, 4)->nullable();
            $table->decimal('porcentaje_iva', 8, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['comunidad_id', 'fecha']);
        });

        Schema::create('diario_apuntes_especiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('tipo_gasto_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->unsignedBigInteger('proveedor_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->nullable()->index();
            $table->string('tipo', 30)->default('extraordinario')->index();
            $table->date('fecha')->index();
            $table->string('descripcion', 200);
            $table->decimal('importe', 15, 4)->default(0);
            $table->decimal('base_imponible', 15, 4)->nullable();
            $table->decimal('porcentaje_iva', 8, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('diario_obras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('tipo_obra_id')->index();
            $table->unsignedBigInteger('tipo_gasto_id')->nullable()->index();
            $table->unsignedBigInteger('banco_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->unsignedBigInteger('proveedor_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->nullable()->index();
            $table->date('fecha')->index();
            $table->string('numero_documento', 50)->nullable();
            $table->string('descripcion', 200);
            $table->decimal('debe', 15, 4)->default(0);
            $table->decimal('haber', 15, 4)->default(0);
            $table->decimal('base_imponible', 15, 4)->nullable();
            $table->decimal('porcentaje_iva', 8, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['comunidad_id', 'tipo_obra_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diario_obras');
        Schema::dropIfExists('diario_apuntes_especiales');
        Schema::dropIfExists('diario_apuntes');
        Schema::dropIfExists('tipos_obra');
    }
};
