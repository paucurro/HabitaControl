<?php

namespace App\Actions\Diario;

use App\Models\Comunidad;
use App\Models\DiarioApunte;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EliminarApunteDiario
{
    public function handle(Comunidad $comunidad, int $apunteId): void
    {
        DB::transaction(function () use ($comunidad, $apunteId): void {
            $apunte = DiarioApunte::query()
                ->whereBelongsTo($comunidad)
                ->lockForUpdate()
                ->findOrFail($apunteId);

            if ($apunte->liquidacion_id !== null) {
                throw ValidationException::withMessages([
                    'apunte' => 'Un apunte liquidado no se puede eliminar.',
                ]);
            }

            $apunte->delete();
        });
    }
}
