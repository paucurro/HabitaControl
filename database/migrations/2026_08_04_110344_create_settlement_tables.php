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
        Schema::create('liquidaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->date('desde')->index();
            $table->date('hasta')->index();
            $table->date('fecha_emision')->index();
            $table->string('descripcion', 200)->nullable();
            $table->string('estado', 30)->default('borrador')->index();
            $table->dateTime('completada_at')->nullable();
            $table->json('datos_empresa')->nullable();
            $table->json('datos_comunidad')->nullable();
            $table->json('datos_banco')->nullable();
            $table->text('texto')->nullable();
            $table->unsignedSmallInteger('plazo_maximo_pago_dias')->nullable();
            $table->decimal('penalizacion', 8, 4)->default(0);
            $table->decimal('fondo_reserva', 15, 4)->default(0);
            $table->boolean('imprimir_nombres')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('liquidacion_lineas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->unsignedBigInteger('diario_apunte_id')->nullable()->index();
            $table->unsignedBigInteger('diario_apunte_especial_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->unsignedBigInteger('banco_id')->nullable()->index();
            $table->unsignedBigInteger('tipo_gasto_id')->nullable()->index();
            $table->string('operacion', 30)->nullable()->index();
            $table->date('fecha')->index();
            $table->string('numero_documento', 50)->nullable();
            $table->string('descripcion', 200);
            $table->decimal('importe', 15, 4)->default(0);
            $table->decimal('coeficiente', 12, 8)->default(0);
            $table->string('tipo_gasto_codigo', 20)->nullable();
            $table->string('tipo_gasto_descripcion', 200)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['liquidacion_id', 'parte_id', 'operacion']);
        });

        Schema::create('liquidacion_partes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('parte_id')->index();
            $table->string('codigo', 20)->nullable();
            $table->string('descripcion', 250)->nullable();
            $table->decimal('deposito', 15, 4)->default(0);
            $table->decimal('coeficiente', 12, 8)->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['liquidacion_id', 'parte_id']);
        });

        Schema::create('liquidacion_propietarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('propietario_id')->index();
            $table->json('datos_propietario');
            $table->boolean('enviar_email')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['liquidacion_id', 'propietario_id']);
        });

        Schema::create('liquidacion_parte_propietario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->unsignedBigInteger('parte_id')->index();
            $table->unsignedBigInteger('propietario_id')->index();
            $table->boolean('imprimir_etiqueta')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['liquidacion_id', 'parte_id', 'propietario_id'], 'liq_parte_prop_unique');
        });

        Schema::create('liquidacion_estados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->boolean('es_titulo')->default(false);
            $table->string('descripcion', 200)->nullable();
            $table->decimal('importe', 15, 4)->default(0);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('liquidacion_envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('liquidacion_id')->index();
            $table->unsignedBigInteger('liquidacion_propietario_id')->nullable()->index();
            $table->unsignedBigInteger('propietario_id')->nullable()->index();
            $table->string('destinatario', 254);
            $table->string('estado', 30)->default('pendiente')->index();
            $table->text('contenido')->nullable();
            $table->dateTime('enviado_at')->nullable();
            $table->dateTime('leido_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacion_envios');
        Schema::dropIfExists('liquidacion_estados');
        Schema::dropIfExists('liquidacion_parte_propietario');
        Schema::dropIfExists('liquidacion_propietarios');
        Schema::dropIfExists('liquidacion_partes');
        Schema::dropIfExists('liquidacion_lineas');
        Schema::dropIfExists('liquidaciones');
    }
};
