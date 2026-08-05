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
        Schema::create('tipos_gasto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->string('codigo', 20);
            $table->string('descripcion', 200);
            $table->text('notas')->nullable();
            $table->boolean('excluir_de_liquidacion')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['comunidad_id', 'codigo']);
        });

        Schema::create('coeficientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('parte_id')->index();
            $table->unsignedBigInteger('tipo_gasto_id')->index();
            $table->decimal('porcentaje', 12, 8)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parte_id', 'tipo_gasto_id']);
        });

        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->string('descripcion', 200);
            $table->decimal('importe', 15, 4);
            $table->unsignedTinyInteger('dia_cobro')->nullable();
            $table->string('periodicidad', 30)->default('mensual')->index();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
        Schema::dropIfExists('coeficientes');
        Schema::dropIfExists('tipos_gasto');
    }
};
