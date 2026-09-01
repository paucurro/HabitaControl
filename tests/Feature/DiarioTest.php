<?php

use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use App\Models\Parte;
use App\Models\Proveedor;
use App\Models\TipoGasto;
use App\Models\TipoObra;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function diaryCommunity(): array
{
    $user = User::factory()->create();
    $administration = Administracion::factory()->for($user, 'propietario')->create();
    $community = Comunidad::factory()->create(['administracion_id' => $administration->id]);

    return [$user, $community];
}

it('muestra el selector del diario con las comunidades visibles', function () {
    [$user, $community] = diaryCommunity();
    $otherAdministration = Administracion::factory()->create();
    Comunidad::factory()->create(['administracion_id' => $otherAdministration->id]);

    $this->actingAs($user)->get(route('diario.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Diario/Index')
            ->has('comunidades', 1)
            ->where('comunidades.0.id', $community->id));
});

it('unifica los tres diarios y los ordena por fecha con saldo', function () {
    [$user, $community] = diaryCommunity();
    $provider = Proveedor::factory()->create([
        'administracion_id' => $community->administracion_id,
        'nombre' => 'Ascensores Norte',
    ]);
    $part = Parte::factory()->for($community)->create([
        'codigo' => '1-A',
        'descripcion' => 'Ático izquierda',
    ]);
    DiarioApunte::factory()->for($community)->create([
        'fecha' => '2026-01-01',
        'descripcion' => 'Primer apunte',
        'debe' => 100,
        'haber' => 0,
        'parte_id' => $part->id,
    ]);
    $latest = DiarioApunte::factory()->for($community)->create([
        'fecha' => '2026-02-01',
        'descripcion' => 'Segundo apunte',
        'debe' => 0,
        'haber' => 25,
        'parte_id' => $part->id,
        'proveedor_id' => $provider->id,
    ]);

    $this->actingAs($user)->get(route('diario.show', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'orden' => 'desc',
        'parte' => $part,
    ]))->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Diario')
            ->where('filtros.tipo', 'apuntes')
            ->where('filtros.orden', 'desc')
            ->where('saldoSeleccionado', '75.0000')
            ->where('apuntes.data.0.id', $latest->id)
            ->where('apuntes.data.0.saldo', '75.0000')
            ->where('apuntes.data.0.parte.descripcion', 'Ático izquierda')
            ->where('apuntes.data.0.proveedor.nombre', 'Ascensores Norte')
            ->missing('comunidades')
            ->has('catalogos.tiposGasto')
            ->has('catalogos.tiposObra'));
});

it('calcula el saldo final de la comunidad o de la parte con las fechas seleccionadas', function () {
    [$user, $community] = diaryCommunity();
    $selectedPart = Parte::factory()->for($community)->create();
    $otherPart = Parte::factory()->for($community)->create();

    DiarioApunte::factory()->for($community)->create([
        'parte_id' => $selectedPart->id,
        'fecha' => '2026-02-15',
        'debe' => 150,
        'haber' => 0,
    ]);
    DiarioApunte::factory()->for($community)->create([
        'parte_id' => $selectedPart->id,
        'fecha' => '2025-12-31',
        'debe' => 1000,
        'haber' => 0,
    ]);
    DiarioApunte::factory()->for($community)->create([
        'parte_id' => $otherPart->id,
        'fecha' => '2026-02-15',
        'debe' => 500,
        'haber' => 0,
    ]);
    DiarioApunte::factory()->for($community)->create([
        'parte_id' => null,
        'fecha' => '2026-02-15',
        'debe' => 0,
        'haber' => 25,
    ]);

    $filters = [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'desde' => '2026-01-01',
        'hasta' => '2026-03-31',
    ];

    $this->actingAs($user)->get(route('diario.show', [
        ...$filters,
        'parte' => $selectedPart,
    ]))->assertInertia(fn (Assert $page) => $page
        ->where('saldoSeleccionado', '150.0000'));

    $this->actingAs($user)->get(route('diario.show', $filters))
        ->assertInertia(fn (Assert $page) => $page
            ->where('saldoSeleccionado', '-25.0000'));
});

it('excluye los apuntes de partes del diario de la comunidad', function () {
    [$user, $community] = diaryCommunity();
    $part = Parte::factory()->for($community)->create();
    $communityEntry = DiarioApunte::factory()->for($community)->create([
        'parte_id' => null,
        'fecha' => '2026-01-01',
        'debe' => 50,
        'haber' => 0,
    ]);
    DiarioApunte::factory()->for($community)->create([
        'parte_id' => $part->id,
        'fecha' => '2026-01-02',
        'debe' => 100,
        'haber' => 0,
    ]);

    $this->actingAs($user)->get(route('diario.show', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
    ]))->assertInertia(fn (Assert $page) => $page
        ->has('apuntes.data', 1)
        ->where('apuntes.data.0.id', $communityEntry->id)
        ->where('saldoSeleccionado', '50.0000'));
});

it('guarda varios apuntes pegados desde excel', function () {
    [$user, $community] = diaryCommunity();
    $part = Parte::factory()->for($community)->create();
    $expenseType = TipoGasto::factory()->for($community)->create();

    $this->actingAs($user)->post(route('diario.store', $community), [
        'tipo' => 'apuntes',
        'apuntes' => [
            [
                'fecha' => '05/08/2026',
                'parte_id' => $part->id,
                'tipo_gasto_id' => $expenseType->id,
                'descripcion' => 'Cobro cuota',
                'debe' => '1.234,56',
                'haber' => '0',
            ],
            [
                'fecha' => '2026-08-06',
                'descripcion' => 'Pago factura',
                'debe' => '0',
                'haber' => '200,25',
            ],
        ],
    ])->assertRedirect(route('diario.show', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
    ]));

    expect(DiarioApunte::query()->whereBelongsTo($community)->count())->toBe(2)
        ->and(DiarioApunte::query()->whereBelongsTo($community)->where('descripcion', 'Cobro cuota')->firstOrFail())
        ->fecha->toDateString()->toBe('2026-08-05')
        ->debe->toBe('1234.5600');
});

it('edita un apunte no liquidado', function () {
    [$user, $community] = diaryCommunity();
    $entry = DiarioApunte::factory()->for($community)->create([
        'fecha' => '2026-01-01',
        'descripcion' => 'Descripción anterior',
        'debe' => 100,
        'haber' => 0,
        'liquidacion_id' => null,
    ]);

    $this->actingAs($user)->put(route('diario.update', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'apunte' => $entry,
    ]), [
        'tipo' => 'apuntes',
        'apuntes' => [[
            'fecha' => '06/08/2026',
            'descripcion' => 'Descripción actualizada',
            'debe' => '0',
            'haber' => '1.234,56',
        ]],
    ])->assertRedirect();

    expect($entry->fresh())
        ->fecha->toDateString()->toBe('2026-08-06')
        ->descripcion->toBe('Descripción actualizada')
        ->debe->toBe('0.0000')
        ->haber->toBe('1234.5600');
});

it('edita apuntes especiales y de obras no liquidados', function () {
    [$user, $community] = diaryCommunity();
    $workType = TipoObra::factory()->for($community)->create();
    $specialEntry = DiarioApunteEspecial::factory()->for($community)->create([
        'importe' => 100,
        'liquidacion_id' => null,
    ]);
    $workEntry = DiarioObra::factory()->for($community)->create([
        'tipo_obra_id' => $workType->id,
        'debe' => 100,
        'haber' => 0,
        'liquidacion_id' => null,
    ]);

    $this->actingAs($user)->put(route('diario.update', [
        'comunidad' => $community,
        'tipo' => 'especiales',
        'apunte' => $specialEntry,
    ]), [
        'tipo' => 'especiales',
        'apuntes' => [[
            'fecha' => '2026-08-06',
            'descripcion' => 'Especial actualizado',
            'tipo' => 'derrama',
            'importe' => -250,
        ]],
    ])->assertRedirect();

    $this->actingAs($user)->put(route('diario.update', [
        'comunidad' => $community,
        'tipo' => 'obras',
        'apunte' => $workEntry,
    ]), [
        'tipo' => 'obras',
        'apuntes' => [[
            'fecha' => '2026-08-06',
            'descripcion' => 'Obra actualizada',
            'tipo_obra_id' => $workType->id,
            'debe' => 0,
            'haber' => 300,
        ]],
    ])->assertRedirect();

    expect($specialEntry->fresh())
        ->descripcion->toBe('Especial actualizado')
        ->tipo->toBe('derrama')
        ->importe->toBe('-250.0000')
        ->and($workEntry->fresh())
        ->descripcion->toBe('Obra actualizada')
        ->debe->toBe('0.0000')
        ->haber->toBe('300.0000');
});

it('no permite editar apuntes liquidados de comunidad ni de parte', function (bool $belongsToPart) {
    [$user, $community] = diaryCommunity();
    $part = $belongsToPart ? Parte::factory()->for($community)->create() : null;
    $entry = DiarioApunte::factory()->for($community)->create([
        'parte_id' => $part?->id,
        'descripcion' => 'Descripción protegida',
        'liquidacion_id' => 99,
    ]);

    $this->actingAs($user)->put(route('diario.update', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'apunte' => $entry,
    ]), [
        'tipo' => 'apuntes',
        'apuntes' => [[
            'fecha' => '2026-08-06',
            'descripcion' => 'No debe cambiar',
            'debe' => 100,
            'haber' => 0,
            'parte_id' => $part?->id,
        ]],
    ])->assertSessionHasErrors('apunte');

    expect($entry->fresh()?->descripcion)->toBe('Descripción protegida');
})->with([
    'comunidad' => false,
    'parte' => true,
]);

it('traspasa un apunte entre diarios de forma atomica', function () {
    [$user, $community] = diaryCommunity();
    $workType = TipoObra::factory()->for($community)->create();
    $entry = DiarioApunteEspecial::factory()->for($community)->create([
        'descripcion' => 'Derrama fachada',
        'importe' => -450,
    ]);

    $this->actingAs($user)->put(route('diario.transfer', [
        'comunidad' => $community,
        'tipo' => 'especiales',
        'apunte' => $entry,
    ]), [
        'destino' => 'obras',
        'tipo_obra_id' => $workType->id,
    ])->assertRedirect(route('diario.show', [
        'comunidad' => $community,
        'tipo' => 'obras',
    ]));

    expect($entry->fresh()?->trashed())->toBeTrue();
    $workEntry = DiarioObra::query()->whereBelongsTo($community)->firstOrFail();
    expect($workEntry)
        ->tipo_obra_id->toBe($workType->id)
        ->descripcion->toBe('Derrama fachada')
        ->debe->toBe('0.0000')
        ->haber->toBe('450.0000');
});

it('no permite traspasar apuntes liquidados', function () {
    [$user, $community] = diaryCommunity();
    $entry = DiarioApunte::factory()->for($community)->create(['liquidacion_id' => 99]);

    $this->actingAs($user)->put(route('diario.transfer', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'apunte' => $entry,
    ]), ['destino' => 'especiales'])
        ->assertSessionHasErrors('apunte');

    expect($entry->fresh()?->trashed())->toBeFalse()
        ->and(DiarioApunteEspecial::query()->whereBelongsTo($community)->exists())->toBeFalse();
});

it('no permite mover ni eliminar apuntes liquidados de una parte', function () {
    [$user, $community] = diaryCommunity();
    $part = Parte::factory()->for($community)->create();
    $entry = DiarioApunte::factory()->for($community)->create([
        'parte_id' => $part->id,
        'liquidacion_id' => 99,
    ]);

    $this->actingAs($user)->put(route('diario.transfer', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'apunte' => $entry,
    ]), ['destino' => 'especiales'])
        ->assertSessionHasErrors('apunte');

    $this->actingAs($user)->delete(route('diario.destroy', [
        'comunidad' => $community,
        'apunte' => $entry,
    ]))->assertSessionHasErrors('apunte');

    expect($entry->fresh()?->trashed())->toBeFalse();
});

it('elimina un apunte normal no liquidado', function () {
    [$user, $community] = diaryCommunity();
    $entry = DiarioApunte::factory()->for($community)->create(['liquidacion_id' => null]);

    $this->actingAs($user)->delete(route('diario.destroy', [
        'comunidad' => $community,
        'apunte' => $entry,
    ]))->assertRedirect();

    $this->assertSoftDeleted($entry);
});

it('no permite eliminar apuntes liquidados', function () {
    [$user, $community] = diaryCommunity();
    $entry = DiarioApunte::factory()->for($community)->create(['liquidacion_id' => 99]);

    $this->actingAs($user)->delete(route('diario.destroy', [
        'comunidad' => $community,
        'apunte' => $entry,
    ]))->assertSessionHasErrors('apunte');

    $this->assertModelExists($entry);
});

it('no permite eliminar apuntes de otra comunidad', function () {
    [$user, $community] = diaryCommunity();
    $otherCommunity = Comunidad::factory()->create([
        'administracion_id' => $community->administracion_id,
    ]);
    $entry = DiarioApunte::factory()->for($otherCommunity)->create(['liquidacion_id' => null]);

    $this->actingAs($user)->delete(route('diario.destroy', [
        'comunidad' => $community,
        'apunte' => $entry,
    ]))->assertNotFound();

    $this->assertModelExists($entry);
});

it('no permite eliminar apuntes sin permiso de gestion', function () {
    [$manager, $community] = diaryCommunity();
    $viewer = User::factory()->create();
    $community->usuariosAsignados()->attach($viewer, [
        'puede_ver' => true,
        'puede_gestionar' => false,
        'asignado_por_user_id' => $manager->id,
    ]);
    $entry = DiarioApunte::factory()->for($community)->create(['liquidacion_id' => null]);

    $this->actingAs($viewer)->delete(route('diario.destroy', [
        'comunidad' => $community,
        'apunte' => $entry,
    ]))->assertForbidden();

    $this->assertModelExists($entry);
});
