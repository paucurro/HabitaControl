<?php

use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('muestra las comunidades ordenadas con su numero de partes', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    $comunidadConMasPartes = Comunidad::factory()->create([
        'administracion_id' => $administracion->id,
        'codigo' => 'COM-002',
        'nombre' => 'Comunidad B',
        'nif' => 'B00000002',
        'direccion' => 'Calle B, 2',
        'poblacion' => 'Palma',
    ]);
    $comunidadConMenosPartes = Comunidad::factory()->create([
        'administracion_id' => $administracion->id,
        'codigo' => 'COM-001',
        'nombre' => 'Comunidad A',
        'nif' => 'A00000001',
        'direccion' => 'Calle A, 1',
        'poblacion' => 'Inca',
    ]);
    Parte::factory()->count(3)->for($comunidadConMasPartes)->create();
    Parte::factory()->for($comunidadConMenosPartes)->create();

    $this->actingAs($administrador)->get(route('comunidades.index', [
        'sort' => 'partes_count',
        'direction' => 'desc',
    ]))->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Index')
            ->where('orden.columna', 'partes_count')
            ->where('orden.direccion', 'desc')
            ->where('comunidades.data.0.id', $comunidadConMasPartes->id)
            ->where('comunidades.data.0.codigo', 'COM-002')
            ->where('comunidades.data.0.nif', 'B00000002')
            ->where('comunidades.data.0.direccion', 'Calle B, 2')
            ->where('comunidades.data.0.poblacion', 'Palma')
            ->where('comunidades.data.0.partes_count', 3)
            ->where('comunidades.data.1.id', $comunidadConMenosPartes->id));
});

it('rechaza columnas de ordenacion no permitidas', function () {
    $administrador = User::factory()->create();
    $administracion = Administracion::factory()->for($administrador, 'propietario')->create();
    Comunidad::factory()->create(['administracion_id' => $administracion->id]);

    $this->actingAs($administrador)
        ->get(route('comunidades.index', ['sort' => 'deleted_at']))
        ->assertSessionHasErrors('sort');
});
