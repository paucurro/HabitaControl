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
        Schema::create('tipos_deposito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->string('nombre', 200);
            $table->decimal('importe', 15, 4)->default(0);
            $table->boolean('excluir_de_liquidacion')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('tipo_deposito_id')->nullable()->index();
            $table->string('codigo', 20);
            $table->string('descripcion', 200)->nullable();
            $table->decimal('deposito', 15, 4)->default(0);
            $table->decimal('coeficiente_general', 12, 8)->default(0);
            $table->string('orden', 200)->nullable();
            $table->string('tomo', 200)->nullable();
            $table->string('libro', 200)->nullable();
            $table->string('folio', 200)->nullable();
            $table->string('finca', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['comunidad_id', 'codigo']);
        });

        Schema::create('propietarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->string('tipo', 30)->nullable();
            $table->string('nombre', 200);
            $table->string('conyuge', 100)->nullable();
            $table->string('nif', 20)->nullable()->index();
            $table->string('direccion', 200)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('poblacion', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('telefono_trabajo', 30)->nullable();
            $table->string('movil', 30)->nullable();
            $table->text('emails')->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->boolean('domiciliar_recibos')->default(false);
            $table->boolean('enviar_email')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('parte_propietario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->index();
            $table->unsignedBigInteger('propietario_id')->index();
            $table->boolean('imprimir_etiqueta')->default(true);
            $table->boolean('imprimir_liquidacion')->default(true);
            $table->date('desde')->nullable();
            $table->date('hasta')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['parte_id', 'propietario_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parte_propietario');
        Schema::dropIfExists('propietarios');
        Schema::dropIfExists('partes');
        Schema::dropIfExists('tipos_deposito');
    }
};
