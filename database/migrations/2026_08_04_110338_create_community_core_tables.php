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
        Schema::create('comunidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->string('codigo', 20)->unique();
            $table->string('nif', 20)->nullable()->index();
            $table->string('nombre', 200);
            $table->string('direccion', 200)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('presidente_nombre', 100)->nullable();
            $table->string('presidente_telefono', 30)->nullable();
            $table->string('vicepresidente_nombre', 100)->nullable();
            $table->string('vicepresidente_telefono', 30)->nullable();
            $table->string('aseguradora', 100)->nullable();
            $table->string('poliza_seguro', 60)->nullable();
            $table->string('contacto_seguro', 100)->nullable();
            $table->string('telefono_seguro', 30)->nullable();
            $table->decimal('fondo_reserva', 15, 4)->default(0);
            $table->unsignedSmallInteger('copias_informe')->default(1);
            $table->text('texto_liquidacion')->nullable();
            $table->unsignedSmallInteger('plazo_maximo_pago_dias')->nullable();
            $table->decimal('penalizacion', 8, 4)->default(0);
            $table->text('observaciones')->nullable();
            $table->boolean('imprimir_estado')->default(false);
            $table->boolean('imprimir_nombres_resumen')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bancos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->string('nombre', 200)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('telefonos', 200)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->string('codigo_interno', 20)->nullable();
            $table->boolean('es_principal')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->string('nombre', 200);
            $table->string('nif', 22)->nullable()->index();
            $table->string('direccion', 200)->nullable();
            $table->string('direccion_adicional', 200)->nullable();
            $table->string('telefono', 100)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('email', 254)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('bancos');
        Schema::dropIfExists('comunidades');
    }
};
