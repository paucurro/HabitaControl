<?php

namespace App\Jobs;

use App\Actions\SincronizarAdministracionLegacy as Sincronizador;
use App\Models\Administracion;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SincronizarAdministracionLegacy implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 3;

    public function __construct(public int $administracionId)
    {
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(Sincronizador $sincronizador): void
    {
        $administracion = Administracion::query()->findOrFail($this->administracionId);
        $administracion->update([
            'sync_estado' => 'procesando',
            'sync_started_at' => now(),
            'sync_finished_at' => null,
            'sync_error' => null,
        ]);

        $resultado = $sincronizador->handle($administracion);

        $administracion->update([
            'sync_estado' => 'completada',
            'sync_finished_at' => now(),
            'sync_resultado' => $resultado,
            'sync_error' => null,
        ]);
    }

    public function uniqueId(): string
    {
        return (string) $this->administracionId;
    }

    public function failed(?Throwable $exception): void
    {
        Administracion::query()->whereKey($this->administracionId)->update([
            'sync_estado' => 'error',
            'sync_finished_at' => now(),
            'sync_error' => $exception?->getMessage() ?? 'Error desconocido durante la sincronización.',
        ]);
    }
}
