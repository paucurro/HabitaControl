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
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->unsignedBigInteger('reportada_por_propietario_id')->nullable()->index();
            $table->unsignedBigInteger('asignada_a_user_id')->nullable()->index();
            $table->dateTime('fecha')->index();
            $table->string('titulo', 200)->nullable();
            $table->longText('descripcion');
            $table->string('prioridad', 20)->default('normal')->index();
            $table->string('estado', 30)->default('abierta')->index();
            $table->dateTime('resuelta_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('incidencia_seguimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('incidencia_id')->index();
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
        Schema::dropIfExists('incidencia_seguimientos');
        Schema::dropIfExists('incidencias');
    }
};
