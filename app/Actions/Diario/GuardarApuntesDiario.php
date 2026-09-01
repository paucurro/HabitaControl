<?php

namespace App\Actions\Diario;

use App\Models\Comunidad;
use App\Models\DiarioApunte;
use App\Models\DiarioApunteEspecial;
use App\Models\DiarioObra;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GuardarApuntesDiario
{
    public function __construct(private PrepararAtributosApunteDiario $prepararAtributos) {}

    /** @param array<int, array<string, mixed>> $apuntes */
    public function handle(Comunidad $comunidad, string $tipo, array $apuntes): void
    {
        DB::transaction(function () use ($comunidad, $tipo, $apuntes): void {
            $timestamps = ['created_at' => now(), 'updated_at' => now()];
            $filas = collect($apuntes)
                ->map(fn (array $apunte): array => [
                    ...$this->prepararAtributos->handle($tipo, $apunte),
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
}
