<?php

use App\Actions\AceptarInvitacionAcceso;
use App\Actions\CrearInvitacionAcceso;
use App\Actions\CrearInvitacionSubusuario;
use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('separa comunidades por administracion y permisos de subusuario', function () {
    $administrador = User::factory()->create();
    $subusuario = User::factory()->create();
    $otraAdministradora = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $otraAdministracion = Administracion::factory()->for($otraAdministradora, 'propietario')->create();
    $comunidadVisible = Comunidad::factory()->create(['administracion_id' => $administracion->id]);
    $comunidadNoAsignada = Comunidad::factory()->create(['administracion_id' => $administracion->id]);
    $comunidadAjena = Comunidad::factory()->create(['administracion_id' => $otraAdministracion->id]);

    $administracion->usuarios()->attach($subusuario, ['rol' => 'subusuario']);
    $comunidadVisible->usuariosAsignados()->attach($subusuario, [
        'puede_ver' => true,
        'puede_gestionar' => false,
        'asignado_por_user_id' => $administrador->id,
    ]);

    expect($administrador->canManageComunidad($comunidadVisible))->toBeTrue()
        ->and($administrador->canViewComunidad($comunidadNoAsignada))->toBeTrue()
        ->and($administrador->canViewComunidad($comunidadAjena))->toBeFalse()
        ->and($subusuario->canViewComunidad($comunidadVisible))->toBeTrue()
        ->and($subusuario->canManageComunidad($comunidadVisible))->toBeFalse()
        ->and($subusuario->canViewComunidad($comunidadNoAsignada))->toBeFalse()
        ->and(Comunidad::query()->visibleTo($subusuario)->pluck('id')->all())
        ->toBe([$comunidadVisible->id]);
});

it('activa el acceso web de propietario mediante una invitacion de un solo uso', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $propietario = Propietario::factory()->create([
        'administracion_id' => $administracion->id,
        'emails' => 'propietario@example.com',
    ]);

    $resultado = app(CrearInvitacionAcceso::class)->handle($administracion, $administrador, $propietario);
    $user = app(AceptarInvitacionAcceso::class)->handle(
        $resultado['token'],
        'Propietario Invitado',
        'Password@12345',
    );

    expect($resultado['invitacion']->token_hash)->not->toBe($resultado['token'])
        ->and($user->email)->toBe('propietario@example.com')
        ->and(Hash::check('Password@12345', $user->password))->toBeTrue()
        ->and($propietario->fresh()->user_id)->toBe($user->id)
        ->and($propietario->fresh()->acceso_web)->toBeTrue()
        ->and($resultado['invitacion']->fresh()->accepted_at)->not->toBeNull();
});

it('incorpora un subusuario invitado y permite asignarle una comunidad', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $comunidad = Comunidad::factory()->create(['administracion_id' => $administracion->id]);

    $resultado = app(CrearInvitacionSubusuario::class)->handle(
        $administracion,
        $administrador,
        'colaborador@example.com',
    );
    $subusuario = app(AceptarInvitacionAcceso::class)->handle(
        $resultado['token'],
        'Colaborador',
        'Password@12345',
    );

    $this->actingAs($administrador)->put(route('administracion.usuarios.comunidades.update', [$subusuario, $comunidad]), [
        'puede_ver' => true,
        'puede_gestionar' => true,
    ])->assertRedirect();

    expect($administracion->usuarios()->whereKey($subusuario->id)->wherePivot('rol', 'subusuario')->exists())->toBeTrue()
        ->and($subusuario->canManageComunidad($comunidad))->toBeTrue();
});

it('impide que un subusuario gestione los usuarios de la administracion', function () {
    $administrador = User::factory()->create();
    $subusuario = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $administracion->usuarios()->attach($subusuario, ['rol' => 'subusuario']);

    $this->actingAs($subusuario)->get(route('administracion.usuarios.index'))->assertForbidden();
});
