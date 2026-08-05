<?php

use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use App\Models\Parte;
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
    DiarioApunte::factory()->for($community)->create([
        'fecha' => '2026-01-01',
        'descripcion' => 'Primer apunte',
        'debe' => 100,
        'haber' => 0,
    ]);
    $latest = DiarioApunte::factory()->for($community)->create([
        'fecha' => '2026-02-01',
        'descripcion' => 'Segundo apunte',
        'debe' => 0,
        'haber' => 25,
    ]);

    $this->actingAs($user)->get(route('diario.show', [
        'comunidad' => $community,
        'tipo' => 'apuntes',
        'orden' => 'desc',
    ]))->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Diario')
            ->where('filtros.tipo', 'apuntes')
            ->where('filtros.orden', 'desc')
            ->where('saldoComunidad', '75.0000')
            ->where('apuntes.data.0.id', $latest->id)
            ->where('apuntes.data.0.saldo', '75.0000')
            ->has('catalogos.tiposGasto')
            ->has('catalogos.tiposObra'));
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
