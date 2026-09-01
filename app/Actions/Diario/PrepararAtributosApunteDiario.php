<?php

namespace App\Actions\Diario;

use Illuminate\Support\Arr;
use InvalidArgumentException;

class PrepararAtributosApunteDiario
{
    /**
     * @param  array<string, mixed>  $apunte
     * @return array<string, mixed>
     */
    public function handle(string $tipo, array $apunte): array
    {
        $common = Arr::only($apunte, [
            'tipo_gasto_id', 'parte_id', 'proveedor_id', 'fecha', 'descripcion',
            'base_imponible', 'porcentaje_iva',
        ]);
        $common['fecha_calculada'] = false;

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
