<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipoGastoRequest;
use App\Http\Requests\UpdateTipoGastoRequest;
use App\Models\Comunidad;
use App\Models\TipoGasto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TipoGastoController extends Controller
{
    public function index(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);
        return Inertia::render('Comunidades/TiposGasto', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'tiposGasto' => $comunidad->tiposGasto()->orderBy('codigo')->get(),
        ]);
    }

    public function store(StoreTipoGastoRequest $request, Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        $comunidad->tiposGasto()->create($request->validated());

        $this->flashSuccess('Tipo de gasto creado.');

        return back();
    }

    public function update(UpdateTipoGastoRequest $request, TipoGasto $tipoGasto): RedirectResponse
    {
        Gate::authorize('update', $tipoGasto->comunidad);
        $tipoGasto->update($request->validated());

        $this->flashSuccess('Tipo de gasto actualizado.');

        return back();
    }

    public function destroy(TipoGasto $tipoGasto): RedirectResponse
    {
        Gate::authorize('update', $tipoGasto->comunidad);
        $tipoGasto->delete();

        $this->flashSuccess('Tipo de gasto archivado.');

        return back();
    }
}
