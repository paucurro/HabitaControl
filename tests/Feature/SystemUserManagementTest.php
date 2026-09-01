<?php

use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\User;
use App\Notifications\InvitacionAccesoNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

test('only user one can manage system users', function () {
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $otherUser = User::factory()->create();

    $this->actingAs($superuser)
        ->get(route('sistema.usuarios.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Sistema/Usuarios')
            ->has('usuarios')
            ->has('administraciones'));

    $this->actingAs($otherUser)
        ->get(route('sistema.usuarios.index'))
        ->assertForbidden();
});

test('user one can invite an administrator', function () {
    Notification::fake();
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $administracion = Administracion::factory()->create();

    $this->actingAs($superuser)
        ->post(route('sistema.usuarios.store'), [
            'name' => 'Nueva administradora',
            'email' => 'nueva@example.com',
            'tipo' => 'administrador',
            'administracion_id' => $administracion->id,
        ])
        ->assertRedirect();

    expect(User::query()->where('email', 'nueva@example.com')->value('role'))->toBe('administrador');
    expect(InvitacionAcceso::query()->where('email', 'nueva@example.com')->value('tipo'))->toBe('administrador');
    Notification::assertSentOnDemand(InvitacionAccesoNotification::class);
});

test('user one can impersonate another user and safely return', function () {
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $target = User::factory()->create();

    $this->actingAs($superuser)
        ->post(route('sistema.suplantacion.store'), ['user_id' => $target->id])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('impersonator_user_id', 1);
    $this->assertAuthenticatedAs($target);

    $this->delete(route('sistema.suplantacion.destroy'))
        ->assertRedirect(route('sistema.usuarios.index'))
        ->assertSessionMissing('impersonator_user_id');
    $this->assertAuthenticatedAs($superuser);
});

test('user one can change a user role and administration access', function () {
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $user = User::factory()->create();
    $administracion = Administracion::factory()->create();

    $this->actingAs($superuser)
        ->put(route('sistema.usuarios.update', $user), [
            'role' => 'administrador',
            'tipo_acceso' => 'administrador',
            'administracion_id' => $administracion->id,
        ])
        ->assertRedirect();

    expect($user->refresh()->role)->toBe('administrador');
    expect($user->administraciones()->whereKey($administracion)->wherePivot('rol', 'administrador')->exists())
        ->toBeTrue();
});

test('an impersonated user cannot start another impersonation', function () {
    $superuser = User::factory()->create(['id' => 1, 'role' => 'superusuario']);
    $firstTarget = User::factory()->create();
    $secondTarget = User::factory()->create();

    $this->actingAs($superuser)->post(route('sistema.suplantacion.store'), ['user_id' => $firstTarget->id]);

    $this->post(route('sistema.suplantacion.store'), ['user_id' => $secondTarget->id])
        ->assertForbidden();
    $this->assertAuthenticatedAs($firstTarget);
});
