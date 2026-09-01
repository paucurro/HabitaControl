<?php

use App\Jobs\SincronizarAdministracionLegacy;
use App\Models\Administracion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

test('only user one can open the legacy synchronization screen', function () {
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $otherUser = User::factory()->create();

    $this->actingAs($superuser)
        ->get(route('sistema.sincronizacion.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sistema/Sincronizacion')
            ->has('administraciones'));

    $this->actingAs($otherUser)
        ->get(route('sistema.sincronizacion.index'))
        ->assertForbidden();
});

test('user one can securely configure and queue a synchronization', function () {
    Queue::fake();
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $administracion = Administracion::factory()->create();

    $this->actingAs($superuser)
        ->put(route('sistema.sincronizacion.update', $administracion), [
            'db_host' => 'localhost',
            'db_port' => 3306,
            'db_origen' => 'legacy_database',
            'db_usuario' => 'legacy_user',
            'db_password' => 'legacy-secret',
        ])
        ->assertRedirect();

    expect(DB::table('administraciones')->whereKey($administracion->id)->value('db_password'))
        ->not->toBe('legacy-secret');

    $this->actingAs($superuser)
        ->post(route('sistema.sincronizacion.store', $administracion))
        ->assertRedirect();

    Queue::assertPushed(
        SincronizarAdministracionLegacy::class,
        fn (SincronizarAdministracionLegacy $job): bool => $job->administracionId === $administracion->id,
    );
});

test('another user cannot configure or start synchronization', function () {
    Queue::fake();
    $user = User::factory()->create();
    $administracion = Administracion::factory()->create();

    $this->actingAs($user)
        ->put(route('sistema.sincronizacion.update', $administracion), ['db_origen' => 'legacy'])
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('sistema.sincronizacion.store', $administracion))
        ->assertForbidden();

    Queue::assertNothingPushed();
});
