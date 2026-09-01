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

class ActualizarApunteDiario
{
    public function __construct(private PrepararAtributosApunteDiario $prepararAtributos) {}

    /** @param array<string, mixed> $datos */
    public function handle(Comunidad $comunidad, string $tipo, int $apunteId, array $datos): void
    {
        DB::transaction(function () use ($comunidad, $tipo, $apunteId, $datos): void {
            $apunte = $this->findEntry($comunidad, $tipo, $apunteId);

            if ($apunte->getAttribute('liquidacion_id') !== null) {
                throw ValidationException::withMessages([
                    'apunte' => 'Un apunte liquidado no se puede editar.',
                ]);
            }

            $apunte->update($this->prepararAtributos->handle($tipo, $datos));
        });
    }

    private function findEntry(Comunidad $comunidad, string $tipo, int $apunteId): Model
    {
        return match ($tipo) {
            'apuntes' => DiarioApunte::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            'especiales' => DiarioApunteEspecial::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            'obras' => DiarioObra::query()->whereBelongsTo($comunidad)->lockForUpdate()->findOrFail($apunteId),
            default => throw new InvalidArgumentException('Tipo de diario no válido.'),
        };
    }
}
