<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipoDepositoRequest;
use App\Http\Requests\UpdateTipoDepositoRequest;
use App\Models\Comunidad;
use App\Models\TipoDeposito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TipoDepositoController extends Controller
{
    public function index(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);

        return Inertia::render('Comunidades/TiposDeposito', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'tiposDeposito' => $comunidad->tiposDeposito()->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreTipoDepositoRequest $request, Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        $comunidad->tiposDeposito()->create($request->validated());

        $this->flashSuccess('Tipo de depósito creado.');

        return back();
    }

    public function update(UpdateTipoDepositoRequest $request, TipoDeposito $tipoDeposito): RedirectResponse
    {
        Gate::authorize('update', $tipoDeposito->comunidad);
        $tipoDeposito->update($request->validated());

        $this->flashSuccess('Tipo de depósito actualizado.');

        return back();
    }

    public function destroy(TipoDeposito $tipoDeposito): RedirectResponse
    {
        Gate::authorize('update', $tipoDeposito->comunidad);
        $tipoDeposito->delete();

        $this->flashSuccess('Tipo de depósito archivado.');

        return back();
    }
}
