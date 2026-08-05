<?php

use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\Propietario;
use App\Models\User;
use App\Notifications\ComunicadoIndividualNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('guarda y muestra la ficha completa de una comunidad con sus cuentas bancarias', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->post(route('comunidades.store'), [
        'codigo' => 'COM-030',
        'nombre' => 'Andreu Feliu 30',
        'nif' => 'E07272040',
        'direccion' => 'C/ Andreu Feliu nº 30',
        'codigo_postal' => '07010',
        'poblacion' => 'Palma de Mallorca',
        'provincia' => 'Illes Balears',
        'pais' => 'España',
        'presidente_nombre' => 'Bernat Bosch 7ºA',
        'presidente_telefono' => '616 843 824',
        'vicepresidente_nombre' => 'Miguel Aguiló 1ºB',
        'vicepresidente_telefono' => '616 843 824',
        'aseguradora' => 'Santa Lucía',
        'poliza_seguro' => '36305',
        'contacto_seguro' => 'Oficina Palma',
        'telefono_seguro' => '91 365 24 24',
        'fondo_reserva' => '1500.2500',
        'copias_informe' => 1,
        'modelo_impresion' => 'Configuración informe de liquidación',
        'texto_liquidacion' => 'Texto para la liquidación.',
        'plazo_maximo_pago_dias' => null,
        'penalizacion' => '2.5000',
        'ano_construccion' => 1973,
        'iee' => 'Inspección favorable. Próxima revisión en 2029.',
        'imprimir_estado' => true,
        'imprimir_nombres_resumen' => false,
        'observaciones' => 'Observaciones de la comunidad.',
        'banco_principal' => 1,
        'bancos' => [
            [
                'nombre' => 'Bankia',
                'codigo_interno' => '541001',
                'iban' => 'ES1200000000000000000001',
                'bic' => 'BANKESMMXXX',
                'direccion' => 'Calle Uno, 1',
                'codigo_postal' => '07001',
                'poblacion' => 'Palma',
                'provincia' => 'Illes Balears',
                'telefonos' => '971 000 001',
            ],
            [
                'nombre' => 'Caixabank',
                'codigo_interno' => '541003',
                'iban' => 'ES2100000000000000000002',
                'bic' => 'CAIXESBBXXX',
                'direccion' => 'Riera, 54',
                'codigo_postal' => '07010',
                'poblacion' => 'Palma',
                'provincia' => 'Illes Balears',
                'telefonos' => '971 764 774',
            ],
        ],
    ])->assertRedirect();

    $comunidad = Comunidad::query()->where('codigo', 'COM-030')->firstOrFail();

    expect($comunidad)
        ->modelo_impresion->toBe('Configuración informe de liquidación')
        ->ano_construccion->toBe(1973)
        ->iee->toContain('2029')
        ->and($comunidad->bancos)->toHaveCount(2)
        ->and($comunidad->bancos()->where('es_principal', true)->value('nombre'))->toBe('Caixabank');

    $this->actingAs($user)->get(route('comunidades.show', $comunidad))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Comunidades/Show')
            ->where('comunidad.ano_construccion', 1973)
            ->where('comunidad.modelo_impresion', 'Configuración informe de liquidación')
            ->has('comunidad.bancos', 2));

    $this->actingAs($user)->get(route('comunidades.edit', $comunidad))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Comunidades/Form')
            ->has('comunidad.bancos', 2));

    $this->actingAs($user)->put(route('comunidades.update', $comunidad), [
        'codigo' => 'COM-030',
        'nombre' => 'Andreu Feliu 30',
        'modelo_impresion' => 'Informe actualizado',
        'ano_construccion' => 1974,
        'iee' => 'IEE actualizada.',
        'imprimir_estado' => false,
        'imprimir_nombres_resumen' => true,
        'banco_principal' => 0,
        'bancos' => [[
            'nombre' => 'Caixabank actualizada',
            'codigo_interno' => '541003',
            'iban' => 'ES2100000000000000000002',
            'bic' => 'CAIXESBBXXX',
            'direccion' => null,
            'codigo_postal' => null,
            'poblacion' => null,
            'provincia' => null,
            'telefonos' => null,
        ]],
    ])->assertRedirect(route('comunidades.show', $comunidad));

    $comunidad->refresh();

    expect($comunidad)
        ->modelo_impresion->toBe('Informe actualizado')
        ->ano_construccion->toBe(1974)
        ->imprimir_estado->toBeFalse()
        ->imprimir_nombres_resumen->toBeTrue()
        ->and($comunidad->bancos)->toHaveCount(1)
        ->and($comunidad->bancos->first()->nombre)->toBe('Caixabank actualizada')
        ->and($comunidad->bancos->first()->es_principal)->toBeTrue();
});

it('importa las partes y propietarios al crear la comunidad', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $csv = "parte_codigo;propietario_nombre;propietario_nif;email;parte_descripcion;coeficiente\n1-A;Ana Pérez;12345678Z;ana@example.com;Vivienda;12.5\n";

    $this->actingAs($user)->post(route('comunidades.store'), [
        'codigo' => 'COM-IMPORT',
        'nombre' => 'Comunidad importada',
        'archivo' => UploadedFile::fake()->createWithContent('partes.csv', $csv),
    ])->assertRedirect();

    $comunidad = Comunidad::query()->where('codigo', 'COM-IMPORT')->firstOrFail();
    $parte = $comunidad->partes()->where('codigo', '1-A')->firstOrFail();

    expect($parte)
        ->descripcion->toBe('Vivienda')
        ->coeficiente_general->toBe('12.50000000')
        ->and($parte->propietarios)->toHaveCount(1)
        ->and($parte->propietarios->first()->nombre)->toBe('Ana Pérez')
        ->and($parte->propietarios->first()->nif)->toBe('12345678Z');
});

it('muestra las partes de la comunidad en una pantalla separada de la ficha', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comunidad = Comunidad::factory()->create();
    $otraComunidad = Comunidad::factory()->create();
    $propietario = Propietario::factory()->create([
        'movil' => '600 123 456',
        'telefono' => '971 123 456',
    ]);
    $primeraParte = Parte::factory()->for($comunidad)->create(['codigo' => '1-A']);
    Parte::factory()->for($comunidad)->create(['codigo' => '2-A']);
    Parte::factory()->for($otraComunidad)->create();
    $primeraParte->propietarios()->attach($propietario);

    $this->actingAs($user)->get(route('comunidades.show', $comunidad))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Show')
            ->missing('comunidad.partes'));

    $this->actingAs($user)->get(route('comunidades.partes.index', $comunidad))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Comunidades/Partes')
            ->where('comunidad.id', $comunidad->id)
            ->has('partes', 2)
            ->has('partes.0.propietarios', 1)
            ->where('partes.0.propietarios.0.movil', '600 123 456')
            ->where('partes.0.propietarios.0.telefono', '971 123 456')
            ->missing('partes.0.tipo_deposito'));
});

it('revierte el alta si el archivo de importacion no tiene las columnas obligatorias', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $csv = "codigo;nombre\n1-A;Ana Pérez\n";

    $this->actingAs($user)->post(route('comunidades.store'), [
        'codigo' => 'COM-INVALIDA',
        'nombre' => 'Comunidad no creada',
        'archivo' => UploadedFile::fake()->createWithContent('partes.csv', $csv),
    ])->assertSessionHasErrors('archivo');

    expect(Comunidad::query()->where('codigo', 'COM-INVALIDA')->exists())->toBeFalse();
});

it('gestiona una comunidad y relaciona una parte con varios propietarios', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comunidad = Comunidad::factory()->create();
    $propietarios = Propietario::factory()->count(2)->create();

    $this->actingAs($user)->post(route('comunidades.partes.store', $comunidad), [
        'comunidad_id' => $comunidad->id,
        'codigo' => '1-A',
        'descripcion' => 'Vivienda primera A',
        'propietario_ids' => $propietarios->modelKeys(),
    ])->assertRedirect();

    $parte = Parte::where('codigo', '1-A')->firstOrFail();
    expect($parte->propietarios)->toHaveCount(2);

    $this->actingAs($user)->get(route('partes.edit', $parte))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Partes/Form')
            ->has('propietarios', 2)
            ->has('parte.propietarios', 2));

    $this->actingAs($user)->get(route('propietarios.show', $propietarios->first()))
        ->assertSuccessful()->assertInertia(fn ($page) => $page->component('Propietarios/Show')->has('propietario.partes', 1));
});

it('importa y exporta los datos de una comunidad en csv compatible con excel', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comunidad = Comunidad::factory()->create();
    $csv = "parte_codigo;propietario_nombre;propietario_nif;email;parte_descripcion;coeficiente\n1-A;Ana Pérez;12345678Z;ana@example.com;Vivienda;12.5\n";

    $this->actingAs($user)->post(route('comunidades.importar', $comunidad), [
        'archivo' => UploadedFile::fake()->createWithContent('datos.csv', $csv),
    ])->assertRedirect();

    expect($comunidad->partes()->count())->toBe(1)
        ->and(Propietario::where('nif', '12345678Z')->exists())->toBeTrue();

    $this->actingAs($user)->get(route('comunidades.exportar', $comunidad))
        ->assertSuccessful()->assertHeader('content-type', 'text/csv; charset=UTF-8');
});

it('envia un comunicado individual a cada propietario seleccionado', function () {
    Notification::fake();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $comunidad = Comunidad::factory()->create();
    $propietario = Propietario::factory()->create(['emails' => 'ana@example.com']);

    $this->actingAs($user)->post(route('comunicados.store'), [
        'comunidad_id' => $comunidad->id,
        'asunto' => 'Reunión',
        'contenido' => 'La reunión será el viernes.',
        'propietario_ids' => [$propietario->id],
    ])->assertRedirect(route('comunicados.index'));

    Notification::assertSentOnDemand(ComunicadoIndividualNotification::class);
});
