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
        Schema::create('administraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propietario_user_id')->constrained('users')->restrictOnDelete();
            $table->string('nombre', 200);
            $table->string('slug', 100)->unique();
            $table->boolean('activa')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('administracion_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administracion_id')->constrained('administraciones')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('rol', 30)->default('subusuario')->index();
            $table->boolean('puede_gestionar_usuarios')->default(false);
            $table->timestamps();
            $table->unique(['administracion_id', 'user_id']);
        });

        Schema::create('comunidad_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comunidad_id')->constrained('comunidades')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('puede_ver')->default(true);
            $table->boolean('puede_gestionar')->default(false);
            $table->foreignId('asignado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['comunidad_id', 'user_id']);
        });

        Schema::create('invitaciones_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administracion_id')->constrained('administraciones')->cascadeOnDelete();
            $table->foreignId('invitada_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('propietario_id')->nullable()->constrained('propietarios')->cascadeOnDelete();
            $table->string('email', 254)->index();
            $table->string('tipo', 30)->default('propietario')->index();
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['administracion_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitaciones_acceso');
        Schema::dropIfExists('comunidad_user');
        Schema::dropIfExists('administracion_user');
        Schema::dropIfExists('administraciones');
    }
};
