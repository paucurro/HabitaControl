<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSincronizacionLegacyRequest;
use App\Http\Requests\UpdateSincronizacionLegacyRequest;
use App\Jobs\SincronizarAdministracionLegacy;
use App\Models\Administracion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SincronizacionLegacyController extends Controller
{
    public function index(): Response
    {
        abort_unless(auth()->id() === 1, 403);

        return Inertia::render('Sistema/Sincronizacion', [
            'administraciones' => Administracion::query()->orderBy('nombre')->get()->map(fn (Administracion $administracion): array => [
                'id' => $administracion->id,
                'nombre' => $administracion->nombre,
                'db_host' => $administracion->db_host,
                'db_port' => $administracion->db_port,
                'db_origen' => $administracion->db_origen,
                'db_usuario' => $administracion->db_usuario,
                'tiene_password' => filled($administracion->db_password),
                'sync_estado' => $administracion->sync_estado,
                'sync_started_at' => $administracion->sync_started_at,
                'sync_finished_at' => $administracion->sync_finished_at,
                'sync_resultado' => $administracion->sync_resultado,
                'sync_error' => $administracion->sync_error,
            ]),
        ]);
    }

    public function update(UpdateSincronizacionLegacyRequest $request, Administracion $administracion): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['db_password'] ?? null)) {
            unset($data['db_password']);
        }

        $administracion->update($data);

        return back()->with('success', 'Configuración de origen guardada.');
    }

    public function store(StoreSincronizacionLegacyRequest $request, ?Administracion $administracion = null): RedirectResponse
    {
        $administraciones = $administracion
            ? collect([$administracion])
            : Administracion::query()
                ->whereNotNull('db_origen')
                ->when($request->validated('administracion_ids'), fn ($query, $ids) => $query->whereIn('id', $ids))
                ->get();

        foreach ($administraciones as $item) {
            SincronizarAdministracionLegacy::dispatch($item->id);
        }

        return back()->with('success', 'Sincronización enviada a la cola.');
    }
}
