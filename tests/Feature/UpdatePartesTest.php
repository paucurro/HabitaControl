<?php

use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\Propietario;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('actualiza masivamente las partes y sus propietarios', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $comunidad = Comunidad::factory()->create(['administracion_id' => $administracion->id]);
    $primerPropietario = Propietario::factory()->create(['administracion_id' => $administracion->id, 'nombre' => 'Ana Perez']);
    $segundoPropietario = Propietario::factory()->create(['administracion_id' => $administracion->id, 'nombre' => 'Bernat Serra']);
    $primeraParte = Parte::factory()->for($comunidad)->create(['codigo' => '1-A']);
    $segundaParte = Parte::factory()->for($comunidad)->create(['codigo' => '2-A']);
    $primeraParte->propietarios()->attach($primerPropietario);

    $this->actingAs($administrador)->get(route('comunidades.partes.index', $comunidad))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Partes')
            ->has('partes', 2)
            ->has('propietarios', 2));

    $this->actingAs($administrador)->put(route('comunidades.partes.update_many', $comunidad), [
        'partes' => [
            [
                'id' => $primeraParte->id,
                'codigo' => '1-B',
                'descripcion' => 'Vivienda reformada',
                'coeficiente_general' => '45.75',
                'propietario_ids' => [$segundoPropietario->id],
            ],
            [
                'id' => $segundaParte->id,
                'codigo' => '2-B',
                'descripcion' => null,
                'coeficiente_general' => '54.25',
                'propietario_ids' => [$primerPropietario->id, $segundoPropietario->id],
            ],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($primeraParte->fresh())
        ->codigo->toBe('1-B')
        ->descripcion->toBe('Vivienda reformada')
        ->coeficiente_general->toBe('45.75000000')
        ->and($primeraParte->fresh()->propietarios->modelKeys())->toBe([$segundoPropietario->id])
        ->and($segundaParte->fresh())
        ->codigo->toBe('2-B')
        ->descripcion->toBeNull()
        ->coeficiente_general->toBe('54.25000000')
        ->and($segundaParte->fresh()->propietarios->modelKeys())->toEqualCanonicalizing([
            $primerPropietario->id,
            $segundoPropietario->id,
        ]);
});

it('rechaza partes y propietarios ajenos en la actualizacion masiva', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $otraAdministracion = Administracion::factory()->create();
    $comunidad = Comunidad::factory()->create(['administracion_id' => $administracion->id]);
    $otraComunidad = Comunidad::factory()->create(['administracion_id' => $otraAdministracion->id]);
    $parte = Parte::factory()->for($comunidad)->create(['codigo' => '1-A']);
    $parteAjena = Parte::factory()->for($otraComunidad)->create();
    $propietarioAjeno = Propietario::factory()->create(['administracion_id' => $otraAdministracion->id]);

    $this->actingAs($administrador)->put(route('comunidades.partes.update_many', $comunidad), [
        'partes' => [
            [
                'id' => $parte->id,
                'codigo' => 'MODIFICADA',
                'descripcion' => null,
                'coeficiente_general' => 100,
                'propietario_ids' => [$propietarioAjeno->id],
            ],
            [
                'id' => $parteAjena->id,
                'codigo' => 'AJENA',
                'descripcion' => null,
                'coeficiente_general' => 0,
                'propietario_ids' => [],
            ],
        ],
    ])->assertInvalid(['partes.0.propietario_ids.0', 'partes.1.id']);

    expect($parte->fresh()->codigo)->toBe('1-A');
});
