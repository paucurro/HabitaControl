<?php

$migrationDirectory = dirname(__DIR__, 2).'/database/migrations';
$domainTables = [
    'comunidades',
    'bancos',
    'proveedores',
    'tipos_deposito',
    'partes',
    'propietarios',
    'parte_propietario',
    'tipos_gasto',
    'coeficientes',
    'cuotas',
    'tipos_obra',
    'diario_apuntes',
    'diario_apuntes_especiales',
    'diario_obras',
    'liquidaciones',
    'liquidacion_lineas',
    'liquidacion_partes',
    'liquidacion_propietarios',
    'liquidacion_parte_propietario',
    'liquidacion_estados',
    'liquidacion_envios',
    'comunicados',
    'comunicado_destinatarios',
    'tipos_siniestro',
    'siniestros',
    'siniestro_seguimientos',
    'incidencias',
    'incidencia_seguimientos',
    'agenda_eventos',
    'agenda_evento_participantes',
];

$domainMigrationFiles = glob($migrationDirectory.'/2026_08_04_*_tables.php');
$domainMigrations = implode("\n", array_map(
    static fn (string $file): string => file_get_contents($file),
    $domainMigrationFiles,
));

it('defines every domain table with normalized identifiers and soft deletes', function (string $table) use ($domainMigrations) {
    $tableDefinitionPattern = sprintf(
        "/Schema::create\\('%s'.*?^        \\}\\);/ms",
        preg_quote($table, '/'),
    );

    expect(preg_match($tableDefinitionPattern, $domainMigrations, $matches))->toBe(1);

    $tableDefinition = $matches[0];

    expect($tableDefinition)
        ->toContain("\$table->id();")
        ->toContain("\$table->timestamps();")
        ->toContain("\$table->softDeletes();")
        ->not->toContain("'ind_");
})->with($domainTables);

it('does not define physical foreign key constraints', function () use ($domainMigrations) {
    expect($domainMigrations)
        ->not->toContain('->constrained(')
        ->not->toContain('$table->foreign(');
});
