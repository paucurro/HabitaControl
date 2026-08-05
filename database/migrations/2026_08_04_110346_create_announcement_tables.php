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
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_id')->nullable()->index();
            $table->unsignedBigInteger('comunidad_id')->index();
            $table->unsignedBigInteger('creado_por_user_id')->nullable()->index();
            $table->string('asunto', 200);
            $table->longText('contenido');
            $table->string('estado', 30)->default('borrador')->index();
            $table->dateTime('publicado_at')->nullable()->index();
            $table->dateTime('enviado_at')->nullable();
            $table->json('adjuntos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comunicado_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comunicado_id')->index();
            $table->unsignedBigInteger('propietario_id')->nullable()->index();
            $table->unsignedBigInteger('parte_id')->nullable()->index();
            $table->string('email', 254)->nullable();
            $table->string('estado', 30)->default('pendiente')->index();
            $table->dateTime('enviado_at')->nullable();
            $table->dateTime('leido_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicado_destinatarios');
        Schema::dropIfExists('comunicados');
    }
};
