<?php

use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\Parte;
use App\Models\Propietario;
use App\Models\User;

it('no muestra comunidades al superusuario sin administracion seleccionada', function () {
    $superusuario = User::factory()->create(['role' => 'superusuario']);
    Comunidad::factory()->create();

    expect(Comunidad::query()->visibleTo($superusuario)->count())->toBe(0);
    $this->actingAs($superusuario)->get(route('comunidades.index'))->assertForbidden();
});

it('limita al superusuario a la administracion seleccionada', function () {
    $superusuario = User::factory()->create(['role' => 'superusuario']);
    $administracion = Administracion::factory()->create();
    $otraAdministracion = Administracion::factory()->create();
    $comunidad = Comunidad::factory()->create(['administracion_id' => $administracion->id]);
    Comunidad::factory()->create(['administracion_id' => $otraAdministracion->id]);

    $this->actingAs($superusuario)->put(route('contexto.administracion.update'), [
        'administracion_id' => $administracion->id,
    ])->assertRedirect(route('dashboard'))->assertSessionHas('selected_administracion_id', $administracion->id);

    expect(Comunidad::query()->visibleTo($superusuario)->pluck('id')->all())->toBe([$comunidad->id]);
});

it('busca cada tipo solamente dentro de la administracion activa', function () {
    $superusuario = User::factory()->create(['role' => 'superusuario']);
    $administracion = Administracion::factory()->create();
    $otraAdministracion = Administracion::factory()->create();
    $comunidad = Comunidad::factory()->create(['administracion_id' => $administracion->id, 'nombre' => 'Residencial Palmeras']);
    Comunidad::factory()->create(['administracion_id' => $otraAdministracion->id, 'nombre' => 'Palmeras Ajena']);
    $parte = Parte::factory()->create(['comunidad_id' => $comunidad->id, 'descripcion' => 'Ático Palmeras']);
    Propietario::factory()->create(['administracion_id' => $administracion->id, 'nombre' => 'Palmeras Propietario']);
    DiarioApunte::factory()->create(['comunidad_id' => $comunidad->id, 'parte_id' => $parte->id, 'descripcion' => 'Reparación Palmeras']);

    $response = $this->actingAs($superusuario)
        ->withSession(['selected_administracion_id' => $administracion->id])
        ->getJson(route('buscar', ['q' => 'Palmeras', 'tipo' => 'todos']))
        ->assertSuccessful();

    expect(collect($response->json('resultados'))->pluck('tipo')->unique()->sort()->values()->all())
        ->toBe(['Comunidad', 'Diario', 'Parte', 'Propietario'])
        ->and(collect($response->json('resultados'))->pluck('titulo'))->not->toContain('Palmeras Ajena');
});
