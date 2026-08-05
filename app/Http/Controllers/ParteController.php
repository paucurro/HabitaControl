<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParteRequest;
use App\Http\Requests\UpdateParteRequest;
use App\Http\Requests\UpdatePartesRequest;
use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ParteController extends Controller
{
    public function index(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);
        $partes = $comunidad->partes()
            ->select(['id', 'comunidad_id', 'codigo', 'descripcion', 'coeficiente_general'])
            ->with('propietarios:id,nombre,movil,telefono')
            ->orderBy('codigo')
            ->get();

        return Inertia::render('Comunidades/Partes', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'partes' => $partes,
            'propietarios' => Propietario::query()
                ->where('administracion_id', $comunidad->administracion_id)
                ->select(['id', 'nombre', 'nif'])
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function updateMany(UpdatePartesRequest $request, Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        $partesValidadas = $request->validatedPartes();

        DB::transaction(function () use ($comunidad, $partesValidadas): void {
            $partes = $comunidad->partes()
                ->whereKey(array_column($partesValidadas, 'id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($partesValidadas as $datosParte) {
                $parte = $partes->get($datosParte['id']);

                if (! $parte instanceof Parte) {
                    throw (new ModelNotFoundException)->setModel(Parte::class, [$datosParte['id']]);
                }

                $parte->update(Arr::only($datosParte, ['codigo', 'descripcion', 'coeficiente_general']));
                $parte->propietarios()->syncOrFail($datosParte['propietario_ids']);
            }
        });

        $this->flashSuccess('Partes y propietarios actualizados.');

        return back();
    }

    public function create(Comunidad $comunidad): Response
    {
        Gate::authorize('update', $comunidad);

        return Inertia::render('Partes/Form', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'tiposDeposito' => $comunidad->tiposDeposito()->orderBy('nombre')->get(['id', 'nombre']),
            'propietarios' => Propietario::query()
                ->where('administracion_id', $comunidad->administracion_id)
                ->select('id', 'nombre', 'nif')->orderBy('nombre')->get(),
        ]);
    }

    public function store(StoreParteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $comunidad = Comunidad::query()->findOrFail($data['comunidad_id']);
        Gate::authorize('update', $comunidad);
        $parte = Parte::create(Arr::except($data, 'propietario_ids'));
        $parte->propietarios()->syncOrFail($data['propietario_ids'] ?? []);

        $this->flashSuccess('Parte creada.');

        return to_route('partes.show', $parte);
    }

    public function show(Parte $parte): Response
    {
        Gate::authorize('view', $parte->comunidad);
        $parte->load(['comunidad:id,codigo,nombre', 'tipoDeposito:id,nombre', 'propietarios:id,nombre,nif', 'coeficientes.tipoGasto:id,codigo,descripcion']);

        return Inertia::render('Partes/Show', ['parte' => $parte]);
    }

    public function edit(Parte $parte): Response
    {
        Gate::authorize('update', $parte->comunidad);
        $parte->load('propietarios:id');

        return Inertia::render('Partes/Form', [
            'parte' => $parte,
            'comunidad' => $parte->comunidad()->first(['id', 'codigo', 'nombre']),
            'tiposDeposito' => $parte->comunidad()->first()->tiposDeposito()->orderBy('nombre')->get(['id', 'nombre']),
            'propietarios' => Propietario::query()
                ->where('administracion_id', $parte->comunidad->administracion_id)
                ->select('id', 'nombre', 'nif')->orderBy('nombre')->get(),
        ]);
    }

    public function update(UpdateParteRequest $request, Parte $parte): RedirectResponse
    {
        Gate::authorize('update', $parte->comunidad);
        $data = $request->validated();
        $parte->update(Arr::except($data, 'propietario_ids'));
        $parte->propietarios()->syncOrFail($data['propietario_ids'] ?? []);

        $this->flashSuccess('Parte actualizada.');

        return to_route('partes.show', $parte);
    }

    public function destroy(Parte $parte): RedirectResponse
    {
        Gate::authorize('update', $parte->comunidad);
        $comunidad = $parte->comunidad_id;
        $parte->delete();

        $this->flashSuccess('Parte archivada.');

        return to_route('comunidades.show', $comunidad);
    }
}
