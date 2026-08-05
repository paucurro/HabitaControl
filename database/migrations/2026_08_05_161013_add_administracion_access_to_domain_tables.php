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
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropUnique('comunidades_codigo_unique');
            $table->foreignId('administracion_id')->nullable()->after('user_id')->constrained('administraciones')->restrictOnDelete();
            $table->unique(['administracion_id', 'codigo']);
        });

        Schema::table('propietarios', function (Blueprint $table) {
            $table->foreignId('administracion_id')->nullable()->after('id')->constrained('administraciones')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->after('administracion_id')->constrained()->nullOnDelete();
            $table->boolean('acceso_web')->default(false)->after('enviar_email')->index();
            $table->timestamp('acceso_web_activado_at')->nullable()->after('acceso_web');
        });

        Schema::table('proveedores', function (Blueprint $table) {
            $table->foreignId('administracion_id')->nullable()->after('id')->constrained('administraciones')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('administracion_id');
        });

        Schema::table('propietarios', function (Blueprint $table) {
            $table->dropColumn(['acceso_web', 'acceso_web_activado_at']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('administracion_id');
        });

        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropUnique(['administracion_id', 'codigo']);
            $table->dropConstrainedForeignId('administracion_id');
            $table->unique('codigo');
        });
    }
};
