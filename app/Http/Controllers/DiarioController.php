<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiarioIndexRequest;
use App\Models\Comunidad;
use App\Models\DiarioApunte;
use Inertia\Inertia;
use Inertia\Response;

class DiarioController extends Controller
{
    public function index(DiarioIndexRequest $request, Comunidad $comunidad): Response
    {
        $parteId = $request->integer('parte') ?: null;
        $apunteId = $request->integer('apunte') ?: null;
        abort_if($parteId !== null && ! $comunidad->partes()->whereKey($parteId)->exists(), 404);
        abort_if($apunteId !== null && ! DiarioApunte::query()->whereBelongsTo($comunidad)->whereKey($apunteId)->exists(), 404);

        return Inertia::render('Comunidades/Diario', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'partes' => $comunidad->partes()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
            'filtros' => ['parte' => $parteId, 'apunte' => $apunteId],
            'apuntes' => DiarioApunte::query()->whereBelongsTo($comunidad)
                ->when($parteId, fn ($query) => $query->where('parte_id', $parteId))
                ->when($apunteId, fn ($query) => $query->orderByRaw('id = ? desc', [$apunteId]))
                ->with('parte:id,codigo')->latest('fecha')->latest('id')->paginate(50)
                ->withQueryString(),
        ]);
    }
}
