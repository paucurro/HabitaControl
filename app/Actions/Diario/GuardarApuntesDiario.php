<?php

namespace App\Actions\Diario;

use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GuardarApuntesDiario
{
    /** @param array<int, array<string, mixed>> $apuntes */
    public function handle(Comunidad $comunidad, string $tipo, array $apuntes): void
    {
        DB::transaction(function () use ($comunidad, $tipo, $apuntes): void {
            $timestamps = ['created_at' => now(), 'updated_at' => now()];
            $filas = collect($apuntes)
                ->map(fn (array $apunte): array => [
                    ...$this->attributes($tipo, $apunte),
                    'comunidad_id' => $comunidad->id,
                    ...$timestamps,
                ])
                ->all();

            match ($tipo) {
                'apuntes' => DiarioApunte::query()->insert($filas),
                'especiales' => DiarioApunteEspecial::query()->insert($filas),
                'obras' => DiarioObra::query()->insert($filas),
                default => throw new InvalidArgumentException('Tipo de diario no válido.'),
            };
        });
    }

    /**
     * @param  array<string, mixed>  $apunte
     * @return array<string, mixed>
     */
    private function attributes(string $tipo, array $apunte): array
    {
        $common = Arr::only($apunte, [
            'tipo_gasto_id', 'parte_id', 'proveedor_id', 'fecha', 'descripcion',
            'base_imponible', 'porcentaje_iva',
        ]);

        return match ($tipo) {
            'apuntes' => [
                ...$common,
                ...Arr::only($apunte, ['banco_id', 'numero_documento']),
                'debe' => $apunte['debe'] ?? 0,
                'haber' => $apunte['haber'] ?? 0,
            ],
            'especiales' => [
                ...$common,
                'tipo' => filled($apunte['tipo'] ?? null) ? $apunte['tipo'] : 'extraordinario',
                'importe' => $apunte['importe'],
            ],
            'obras' => [
                ...$common,
                ...Arr::only($apunte, ['tipo_obra_id', 'banco_id', 'numero_documento']),
                'debe' => $apunte['debe'] ?? 0,
                'haber' => $apunte['haber'] ?? 0,
            ],
            default => throw new InvalidArgumentException('Tipo de diario no válido.'),
        };
    }
}
