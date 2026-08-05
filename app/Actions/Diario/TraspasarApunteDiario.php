<?php

namespace App\Actions\Diario;

use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TraspasarApunteDiario
{
    public function handle(Comunidad $comunidad, string $origen, int $apunteId, string $destino, ?int $tipoObraId): void
    {
        DB::transaction(function () use ($comunidad, $origen, $apunteId, $destino, $tipoObraId): void {
            $apunte = $this->findSource($comunidad, $origen, $apunteId);

            if ($apunte->getAttribute('liquidacion_id') !== null) {
                throw ValidationException::withMessages([
                    'apunte' => 'Un apunte liquidado no se puede traspasar.',
                ]);
            }

            $attributes = $this->targetAttributes($apunte, $origen, $destino, $tipoObraId);

            match ($destino) {
                'apuntes' => DiarioApunte::query()->create($attributes),
                'especiales' => DiarioApunteEspecial::query()->create($attributes),
                'obras' => DiarioObra::query()->create($attributes),
                default => throw new InvalidArgumentException('Diario de destino no válido.'),
            };

            $apunte->delete();
        });
    }

    private function findSource(Comunidad $comunidad, string $origen, int $apunteId): Model
    {
        return match ($origen) {
            'apuntes' => DiarioApunte::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            'especiales' => DiarioApunteEspecial::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            'obras' => DiarioObra::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            default => throw new InvalidArgumentException('Diario de origen no válido.'),
        };
    }

    /** @return array<string, mixed> */
    private function targetAttributes(Model $apunte, string $origen, string $destino, ?int $tipoObraId): array
    {
        $common = [
            'comunidad_id' => $apunte->getAttribute('comunidad_id'),
            'tipo_gasto_id' => $apunte->getAttribute('tipo_gasto_id'),
            'parte_id' => $apunte->getAttribute('parte_id'),
            'proveedor_id' => $apunte->getAttribute('proveedor_id'),
            'fecha' => $apunte->getAttribute('fecha'),
            'descripcion' => $apunte->getAttribute('descripcion'),
            'base_imponible' => $apunte->getAttribute('base_imponible'),
            'porcentaje_iva' => $apunte->getAttribute('porcentaje_iva'),
        ];
        $importe = $origen === 'especiales'
            ? (float) $apunte->getAttribute('importe')
            : (float) $apunte->getAttribute('debe') - (float) $apunte->getAttribute('haber');

        if ($destino === 'especiales') {
            return [
                ...$common,
                'tipo' => $origen === 'especiales' ? $apunte->getAttribute('tipo') : 'extraordinario',
                'importe' => $importe,
            ];
        }

        $attributes = [
            ...$common,
            'banco_id' => $origen === 'especiales' ? null : $apunte->getAttribute('banco_id'),
            'numero_documento' => $origen === 'especiales' ? null : $apunte->getAttribute('numero_documento'),
            'debe' => $importe > 0 ? $importe : 0,
            'haber' => $importe < 0 ? abs($importe) : 0,
        ];

        return $destino === 'obras' ? [...$attributes, 'tipo_obra_id' => $tipoObraId] : $attributes;
    }
}
