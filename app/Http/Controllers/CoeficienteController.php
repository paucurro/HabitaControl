<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCoeficientesRequest;
use App\Models\Coeficiente;
use App\Models\Comunidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CoeficienteController extends Controller
{
    public function index(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);

        return Inertia::render('Comunidades/Coeficientes', [
            'comunidad' => $comunidad->only(['id', 'codigo', 'nombre']),
            'partes' => $comunidad->partes()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
            'tiposGasto' => $comunidad->tiposGasto()->orderBy('codigo')->get(['id', 'codigo', 'descripcion']),
            'coeficientes' => Coeficiente::query()->where('comunidad_id', $comunidad->id)->get(['parte_id', 'tipo_gasto_id', 'porcentaje']),
        ]);
    }

    public function update(UpdateCoeficientesRequest $request, Comunidad $comunidad): RedirectResponse
    {
        Gate::authorize('update', $comunidad);
        DB::transaction(function () use ($request, $comunidad): void {
            foreach ($request->validated('coeficientes') as $fila) {
                Coeficiente::updateOrCreate(
                    ['parte_id' => $fila['parte_id'], 'tipo_gasto_id' => $fila['tipo_gasto_id']],
                    ['comunidad_id' => $comunidad->id, 'porcentaje' => $fila['porcentaje']],
                );
            }
        });

        $this->flashSuccess('Coeficientes actualizados.');

        return back();
    }
}
