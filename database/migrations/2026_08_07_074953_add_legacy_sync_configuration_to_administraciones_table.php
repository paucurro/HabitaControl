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
        Schema::table('administraciones', function (Blueprint $table) {
            $table->string('db_host', 255)->nullable()->after('activa');
            $table->unsignedSmallInteger('db_port')->nullable()->after('db_host');
            $table->string('db_origen', 100)->nullable()->after('db_port')->index();
            $table->string('db_usuario', 100)->nullable()->after('db_origen');
            $table->text('db_password')->nullable()->after('db_usuario');
            $table->string('sync_estado', 30)->default('pendiente')->after('db_password')->index();
            $table->timestamp('sync_started_at')->nullable()->after('sync_estado');
            $table->timestamp('sync_finished_at')->nullable()->after('sync_started_at');
            $table->json('sync_resultado')->nullable()->after('sync_finished_at');
            $table->text('sync_error')->nullable()->after('sync_resultado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administraciones', function (Blueprint $table) {
            $table->dropIndex(['db_origen']);
            $table->dropIndex(['sync_estado']);
            $table->dropColumn([
                'db_host', 'db_port', 'db_origen', 'db_usuario', 'db_password',
                'sync_estado', 'sync_started_at', 'sync_finished_at', 'sync_resultado', 'sync_error',
            ]);
        });
    }
};
