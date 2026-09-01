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

it('returns from envelope labels to the community detail', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Comunidades/Etiquetas.vue');

    expect($component)
        ->toContain("import CommunityBackLink from '@/components/CommunityBackLink.vue';")
        ->toContain(':href="showComunidad.url(comunidad.id)"')
        ->toContain('{{ comunidad.nombre }}');
});

it('renders the owners as a contact table with their part count', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Propietarios/Index.vue');

    expect($component)
        ->toContain('<TableHead class="min-w-56">Nombre</TableHead>')
        ->toContain('<TableHead class="min-w-48">Cónyuge</TableHead>')
        ->toContain('<TableHead class="min-w-56">Teléfonos</TableHead>')
        ->toContain('<TableHead class="min-w-56">Email</TableHead>')
        ->toContain('<TableHead class="w-28 text-right"')
        ->toContain('>Partes</TableHead')
        ->toContain('propietario.telefono_trabajo')
        ->toContain('{{ propietario.partes_count }}');
});

it('shows every field from the complete owner record except bank account cc', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Propietarios/Show.vue');
    $createComponent = file_get_contents($resourceDirectory.'/pages/Propietarios/Index.vue');

    expect($component)
        ->toContain('name="conyuge"')
        ->toContain('name="telefono_trabajo"')
        ->toContain('name="pais"')
        ->toContain('name="iban"')
        ->toContain('name="bic"')
        ->toContain('name="tipo"')
        ->toContain('Recibo electrónico')
        ->toContain('name="observaciones"')
        ->not->toContain('C.C. banco');

    expect($createComponent)
        ->toContain('name="conyuge"')
        ->toContain('name="telefono_trabajo"')
        ->toContain('name="iban"')
        ->toContain('name="bic"')
        ->toContain('name="tipo"')
        ->toContain('name="observaciones"')
        ->not->toContain('C.C. banco');
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

it('keeps list controls visible while table rows scroll within the available height', function () use ($resourceDirectory) {
    $header = file_get_contents($resourceDirectory.'/components/AppSidebarHeader.vue');
    $journal = file_get_contents($resourceDirectory.'/pages/Comunidades/Diario.vue');
    $communities = file_get_contents($resourceDirectory.'/pages/Comunidades/Index.vue');
    $owners = file_get_contents($resourceDirectory.'/pages/Propietarios/Index.vue');

    expect($header)
        ->toContain('class="sticky top-0 z-30')
        ->toContain('bg-background');

    expect($journal)
        ->toContain('xl:h-[calc(100svh-5rem)]')
        ->toContain('class="flex min-h-0 flex-1 flex-col overflow-hidden py-0"')
        ->toContain('class="min-h-0 flex-1 overflow-auto"')
        ->toContain('class="sticky top-0 z-10 border-b bg-muted text-left"')
        ->toContain('v-if="entry.parte && !filtros.parte"');

    expect($communities)
        ->toContain('xl:h-[calc(100svh-5rem)]')
        ->toContain('xl:[&>[data-slot=table-container]]:h-full')
        ->toContain('<TableHeader class="sticky top-0 z-10 bg-card">');

    expect($owners)
        ->toContain('xl:h-[calc(100svh-5rem)]')
        ->toContain('xl:[&>[data-slot=table-container]]:h-full')
        ->toContain('<TableHeader class="sticky top-0 z-10 bg-card">')
        ->toContain('xl:min-h-0 xl:overflow-y-auto');
});

it('unifies journals with sortable balances, transfers and excel paste entry', function () use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/Comunidades/Diario.vue');

    expect($component)
        ->toContain("apuntes: 'Diario apuntes'")
        ->toContain("especiales: 'Diario apuntes especiales'")
        ->toContain("obras: 'Diario obras'")
        ->toContain("? 'Saldo de la parte'")
        ->toContain(": 'Saldo de la comunidad'")
        ->toContain('currency(saldoSeleccionado)')
        ->toContain('xl:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto_minmax(11rem,auto)]')
        ->not->toContain('Saldo de la comunidad en este diario')
        ->toContain("filtros.orden === 'asc'")
        ->toContain('<Plus class="size-4" /> Insertar apuntes')
        ->not->toContain('Pegar lista de Excel')
        ->not->toContain('Nuevo apunte')
        ->toContain(".map((line) => line.split('\\t'))")
        ->toContain('while (form.apuntes.length < startRow + matrix.length)')
        ->toContain('const communityOnlyFieldKeys = new Set<FieldKey>')
        ->toContain('const visibleEditorFields = computed')
        ->toContain('fieldAppliesToRow(row, field)')
        ->toContain('clearCommunityOnlyFields(row)')
        ->toContain('Crear líneas de todas las partes')
        ->toContain('Copiar descripción e importe')
        ->toContain('if (copyDescriptionAndAmount.value)')
        ->toContain('row.descripcion = source.descripcion')
        ->toContain('row.debe = source.debe')
        ->toContain('row.haber = source.haber')
        ->toContain('row.importe = source.importe')
        ->toContain('Guardar {{ form.apuntes.length }}')
        ->toContain('h-[90vh] w-[90vw] max-w-[90vw]')
        ->toContain('min-h-0 flex-1 overflow-auto rounded-lg border')
        ->toContain('Traspasar apunte')
        ->toContain(':title="entry.tipo_gasto.descripcion"')
        ->toContain(':title="entry.banco.nombre"')
        ->toContain('[parte.codigo, parte.descripcion]')
        ->not->toContain('`Parte ${parte.codigo}`')
        ->toContain(".join(' · ')")
        ->toContain('{{ parteLabel(entry.parte) }}')
        ->toContain('Prov: {{ entry.proveedor.nombre }}')
        ->toContain('useGrouping: true')
        ->toContain('Ingreso/Pago')
        ->toContain('signedCurrency(entryMovement(entry))')
        ->toContain("return 'text-emerald-600 dark:text-emerald-400'")
        ->toContain("return 'text-destructive'")
        ->toContain('isNegative(entry.saldo)')
        ->toContain('aria-label="Traspasar a otro diario"')
        ->toContain('aria-label="Editar apunte"')
        ->toContain('@click="openEdit(entry)"')
        ->toContain('v-if="entry.liquidacion_id === null"')
        ->toContain("day: '2-digit'")
        ->toContain("month: '2-digit'")
        ->toContain("year: 'numeric'")
        ->toContain('dateFormatter.format(')
        ->toContain("filtros.tipo === 'apuntes'")
        ->toContain('entry.liquidacion_id === null')
        ->toContain('router.delete(')
        ->toContain('destroy({')
        ->toContain('aria-label="Eliminar apunte"')
        ->toContain('<Trash2 class="size-4" />')
        ->not->toContain('<Trash2 class="size-4" /> Eliminar')
        ->not->toContain('<th class="p-3">Parte</th>')
        ->not->toContain('<th class="p-3">Proveedor</th>')
        ->toContain("} from '@/routes/diario';");
});

it('adds the journal to the sidebar with a community selector and change button', function () use ($resourceDirectory) {
    $sidebar = file_get_contents($resourceDirectory.'/components/AppSidebar.vue');
    $selector = file_get_contents($resourceDirectory.'/pages/Diario/Index.vue');
    $journal = file_get_contents($resourceDirectory.'/pages/Comunidades/Diario.vue');
    $communitySelector = file_get_contents($resourceDirectory.'/components/CommunityDiarySelector.vue');

    expect($sidebar)
        ->toContain("import { index as diario } from '@/routes/diario';")
        ->toContain("{ title: 'Diario', href: diario(), icon: BookOpen }");

    expect($selector)
        ->toContain('Selecciona la comunidad cuyo diario quieres consultar.')
        ->toContain('<CommunityDiarySelector :comunidades="comunidades" />')
        ->not->toContain('Abrir diario');

    expect($journal)
        ->toContain('index as diaryIndex')
        ->toContain('title: props.comunidad.nombre')
        ->toContain('description: undefined')
        ->toContain(':href="diaryIndex()"')
        ->toContain('<Building2 class="size-4" />')
        ->toContain('Cambiar de comunidad')
        ->toContain('class="hidden h-7 w-px bg-border sm:block"')
        ->not->toContain(':selected-community-id="comunidad.id"')
        ->not->toContain(':comunidades="comunidades"')
        ->toContain('<option value="">Diario de la comunidad</option>')
        ->not->toContain('<option value="">Todas las partes</option>');

    expect($communitySelector)
        ->toContain("import { show } from '@/routes/diario';")
        ->toContain('router.visit(show(comunidad.id))')
        ->toContain('open-on-click')
        ->toContain('Buscar una comunidad por código o nombre')
        ->toContain('class="grid w-full gap-1.5"');
});

it('renders page titles in the application header instead of page content', function () use ($resourceDirectory) {
    $header = file_get_contents($resourceDirectory.'/components/AppSidebarHeader.vue');

    expect($header)
        ->toContain('<h1 class="truncate text-xl font-semibold tracking-tight">')
        ->toContain('props.title ?? props.breadcrumbs.at(-1)?.title')
        ->toContain('class="ml-auto flex min-w-0 flex-1 basis-0 items-center justify-end gap-3"')
        ->toContain('<GlobalSearch class="hidden min-w-0 flex-1 md:block" />')
        ->toContain('<div class="shrink-0">')
        ->not->toContain('<Breadcrumbs');
});

it('does not duplicate the main title inside authenticated pages', function (string $page) use ($resourceDirectory) {
    $component = file_get_contents($resourceDirectory.'/pages/'.$page);

    expect($component)->not->toContain('<h1');
})->with([
    'administration users' => 'Administracion/Usuarios.vue',
    'notices index' => 'Comunicados/Index.vue',
    'notice form' => 'Comunicados/Form.vue',
    'communities index' => 'Comunidades/Index.vue',
    'community form' => 'Comunidades/Form.vue',
    'community detail' => 'Comunidades/Show.vue',
    'community parts' => 'Comunidades/Partes.vue',
    'community coefficients' => 'Comunidades/Coeficientes.vue',
    'community expense types' => 'Comunidades/TiposGasto.vue',
    'community deposit types' => 'Comunidades/TiposDeposito.vue',
    'community labels' => 'Comunidades/Etiquetas.vue',
    'journal selector' => 'Diario/Index.vue',
    'community journal' => 'Comunidades/Diario.vue',
    'part form' => 'Partes/Form.vue',
    'part detail' => 'Partes/Show.vue',
    'owners index' => 'Propietarios/Index.vue',
    'owner detail' => 'Propietarios/Show.vue',
    'profile settings' => 'settings/Profile.vue',
    'security settings' => 'settings/Security.vue',
    'appearance settings' => 'settings/Appearance.vue',
]);
