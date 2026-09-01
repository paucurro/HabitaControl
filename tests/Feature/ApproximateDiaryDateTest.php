<?php

use App\Actions\Diario\CalcularFechasAproximadas;
use App\Models\Comunidad;
use App\Models\DiarioApunte;

test('it interpolates an invalid diary date between valid neighboring legacy ids', function () {
    $comunidad = Comunidad::factory()->create();

    DiarioApunte::query()->forceCreate([
        'comunidad_id' => $comunidad->id,
        'legacy_source' => 'legacy_test',
        'legacy_id' => 10,
        'fecha' => '2024-01-01',
        'descripcion' => 'Anterior',
        'debe' => 0,
        'haber' => 0,
    ]);
    $invalid = DiarioApunte::query()->forceCreate([
        'comunidad_id' => $comunidad->id,
        'legacy_source' => 'legacy_test',
        'legacy_id' => 20,
        'fecha' => now()->addYear()->toDateString(),
        'descripcion' => 'Fecha inválida',
        'debe' => 0,
        'haber' => 0,
    ]);
    DiarioApunte::query()->forceCreate([
        'comunidad_id' => $comunidad->id,
        'legacy_source' => 'legacy_test',
        'legacy_id' => 30,
        'fecha' => '2024-01-11',
        'descripcion' => 'Posterior',
        'debe' => 0,
        'haber' => 0,
    ]);

    $calculated = app(CalcularFechasAproximadas::class)->handle('legacy_test');

    expect($calculated)->toBe(1)
        ->and($invalid->refresh()->fecha->toDateString())->toBe('2024-01-06')
        ->and($invalid->fecha_calculada)->toBeTrue();
});

test('it does not invent a date without both valid neighbors', function () {
    $comunidad = Comunidad::factory()->create();
    $invalid = DiarioApunte::query()->forceCreate([
        'comunidad_id' => $comunidad->id,
        'legacy_source' => 'legacy_without_bounds',
        'legacy_id' => 20,
        'fecha' => now()->addYear()->toDateString(),
        'descripcion' => 'Sin límites',
        'debe' => 0,
        'haber' => 0,
    ]);

    expect(app(CalcularFechasAproximadas::class)->handle('legacy_without_bounds'))->toBe(0)
        ->and($invalid->refresh()->fecha_calculada)->toBeFalse();
});
