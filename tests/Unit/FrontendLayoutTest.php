<?php

$resourceDirectory = dirname(__DIR__, 2).'/resources/js';

it('uses a compact borderless community back link', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/components/CommunityBackLink.vue');

    expect($component)
        ->toContain('size-5 shrink-0 stroke-[2.5]')
        ->not->toContain(' border');
});

it('makes every part row navigable and displays its id and coefficient total', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Comunidades/Partes.vue');

    expect($component)
        ->toContain(':role="editandoMasivamente ? undefined : \'link\'"')
        ->toContain('@click="goToParte(parte.id)"')
        ->toContain('{{ parte.codigo }} [{{ parte.id }}]')
        ->toContain('Total {{ totalCoeficienteFormateado }}%')
        ->toContain('const decimalesCoeficiente = computed')
        ->toContain('minimumFractionDigits: decimalesCoeficiente.value')
        ->toContain('class="text-right tabular-nums"')
        ->toContain('formatCoeficiente(')
        ->toContain('parte.coeficiente_general,')
        ->toContain('return propietario.movil?.trim() || propietario.telefono?.trim() || null;')
        ->toContain('· {{ telefonoPropietario(propietario) }}')
        ->toContain(':aria-label="`Editar propietario ${propietario.nombre}`"')
        ->not->toContain('Tipo depósito');
});

it('returns from a part detail to the community parts list', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Partes/Show.vue');

    expect($component)
        ->toContain("import { index as partesIndex } from '@/routes/comunidades/partes';")
        ->toContain(':href="partesIndex.url(parte.comunidad.id)"')
        ->toContain('Partes y propietarios · {{ parte.comunidad.nombre }}')
        ->not->toContain(':href="showComunidad.url(parte.comunidad.id)"');
});

it('shows stored expense coefficients as percentages with compact decimals', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Comunidades/Coeficientes.vue');

    expect($component)
        ->toContain('function formatPorcentajeInput(value: string): string')
        ->toContain('function navegarConFlechas(')
        ->toContain("event.key === 'ArrowUp'")
        ->toContain("event.key === 'ArrowDown'")
        ->toContain("event.key === 'ArrowRight'")
        ->toContain(':data-coeficiente-fila="parteIndex"')
        ->toContain(':data-coeficiente-columna="tipoIndex"')
        ->toContain('@keydown="')
        ->toContain('const porcentaje = Number(value) * 100;')
        ->toContain("decimales.replace(/0+$/, '').padEnd(2, '0')")
        ->toContain('porcentaje: (Number(grid[parte.id]?.[tipo.id]) || 0) / 100')
        ->toContain('Introduce los coeficientes como porcentajes (por ejemplo,')
        ->toContain('Usa ↑ y ↓ para cambiar de')
        ->toContain('fila y → para avanzar a la siguiente columna.')
        ->not->toContain('Edita el porcentaje de cada parte para cada tipo de gasto y')
        ->toContain('step="0.000001"')
        ->toContain('class="mx-auto h-8 w-28 text-center"')
        ->toContain('class="text-center tabular-nums"')
        ->toContain('class="text-center"')
        ->toContain('class="min-w-44 py-2 text-center whitespace-normal"')
        ->toContain('[{{ tipo.codigo }}]')
        ->toContain('{{ tipo.descripcion }}');

    expect(substr_count($component, 'navegarConFlechas('))->toBe(2);
});

it('adds history back navigation beside the owner name', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Propietarios/Show.vue');

    expect($component)
        ->toContain('window.history.back()')
        ->toContain('router.visit(propietariosIndex.url())')
        ->toContain('class="cursor-pointer"')
        ->toContain('aria-label="Volver a la pantalla anterior"')
        ->toContain('<ArrowLeft class="size-5" />');
});

it('uses wide containers for management forms', function (string $form) use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/'.$form);

    expect($component)->toContain('max-w-7xl');
})->with([
    'community form' => 'Comunidades/Form.vue',
    'part form' => 'Partes/Form.vue',
    'notice form' => 'Comunicados/Form.vue',
]);

it('uses the full available width for community detail lists', function (string $page) use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/'.$page);

    expect($component)
        ->toContain('<main class="flex flex-1 flex-col gap-6 p-4 md:p-8">')
        ->not->toContain('mx-auto flex w-full max-w-');
})->with([
    'parts' => 'Comunidades/Partes.vue',
    'expense types' => 'Comunidades/TiposGasto.vue',
    'deposit types' => 'Comunidades/TiposDeposito.vue',
]);

it('renders the community index as a sortable table', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Comunidades/Index.vue');

    expect($component)
        ->toContain("{ clave: 'codigo', etiqueta: 'Código'")
        ->toContain("{ clave: 'nombre', etiqueta: 'Nombre'")
        ->toContain("{ clave: 'nif', etiqueta: 'NIF'")
        ->toContain("{ clave: 'direccion', etiqueta: 'Dirección'")
        ->toContain("{ clave: 'poblacion', etiqueta: 'Población'")
        ->toContain("{ clave: 'partes_count', etiqueta: 'Partes'")
        ->toContain(':aria-sort="ariaOrden(columna.clave)"')
        ->toContain(':href="enlaceOrden(columna.clave)"')
        ->toContain('{{ comunidad.nif || \'—\' }}')
        ->toContain("{{ comunidad.direccion || '—' }}")
        ->toContain("{{ comunidad.poblacion || '—' }}")
        ->toContain('{{ comunidad.partes_count }}')
        ->not->toContain('<Building2');
});
