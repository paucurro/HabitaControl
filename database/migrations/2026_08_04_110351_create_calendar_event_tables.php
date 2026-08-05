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
        Schema::create('agenda_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comunidad_id')->nullable()->index();
            $table->unsignedBigInteger('creado_por_user_id')->nullable()->index();
            $table->unsignedBigInteger('incidencia_id')->nullable()->index();
            $table->unsignedBigInteger('siniestro_id')->nullable()->index();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->string('ubicacion', 200)->nullable();
            $table->dateTime('inicio')->index();
            $table->dateTime('fin')->nullable()->index();
            $table->boolean('todo_el_dia')->default(false);
            $table->string('estado', 30)->default('programado')->index();
            $table->string('recurrencia', 100)->nullable();
            $table->dateTime('recordatorio_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('agenda_evento_participantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_evento_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('propietario_id')->nullable()->index();
            $table->string('nombre', 200)->nullable();
            $table->string('email', 254)->nullable();
            $table->string('respuesta', 30)->default('pendiente')->index();
            $table->dateTime('respondido_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_evento_participantes');
        Schema::dropIfExists('agenda_eventos');
    }
};
