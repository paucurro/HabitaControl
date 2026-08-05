<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropietarioRequest;
use App\Http\Requests\UpdatePropietarioRequest;
use App\Models\Propietario;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PropietarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Propietarios/Index', ['propietarios' => Propietario::query()->visibleTo(request()->user())->withCount('partes')->orderBy('nombre')->paginate(25)]);
    }

    public function store(StorePropietarioRequest $request): RedirectResponse
    {
        $administracionId = $request->user()->managedAdministracionId();
        abort_if($administracionId === null, 403);

        $propietario = Propietario::create([...$request->validated(), 'administracion_id' => $administracionId]);

        $this->flashSuccess('Propietario creado.');

        return to_route('propietarios.show', $propietario);
    }

    public function show(Propietario $propietario): Response
    {
        abort_unless(Propietario::query()->visibleTo(request()->user())->whereKey($propietario)->exists(), 403);
        $propietario->load(['partes.comunidad:id,codigo,nombre']);

        return Inertia::render('Propietarios/Show', [
            'propietario' => $propietario,
            'canInvite' => request()->user()->isAdministracionManager($propietario->administracion_id),
            'canManage' => request()->user()->isAdministracionManager($propietario->administracion_id),
        ]);
    }

    public function update(UpdatePropietarioRequest $request, Propietario $propietario): RedirectResponse
    {
        abort_unless($request->user()->isAdministracionManager($propietario->administracion_id), 403);
        $propietario->update($request->validated());

        $this->flashSuccess('Propietario actualizado.');

        return back();
    }

    public function destroy(Propietario $propietario): RedirectResponse
    {
        abort_unless(request()->user()->isAdministracionManager($propietario->administracion_id), 403);
        $propietario->delete();

        $this->flashSuccess('Propietario archivado.');

        return to_route('propietarios.index');
    }
}
