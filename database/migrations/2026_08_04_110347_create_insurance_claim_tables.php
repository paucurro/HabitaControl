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
        Schema::create('tipos_siniestro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->string('descripcion', 200);
            $table->string('aseguradora', 100)->nullable();
            $table->string('poliza', 60)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 254)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('siniestros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('tipo_siniestro_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->date('fecha')->index();
            $table->string('numero_expediente', 100)->nullable()->index();
            $table->longText('descripcion')->nullable();
            $table->string('aseguradora', 100)->nullable();
            $table->string('poliza', 60)->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('estado', 30)->default('abierto')->index();
            $table->dateTime('cerrado_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('siniestro_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('siniestro_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->longText('nota');
            $table->json('adjuntos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siniestro_seguimientos');
        Schema::dropIfExists('siniestros');
        Schema::dropIfExists('tipos_siniestro');
    }
};
