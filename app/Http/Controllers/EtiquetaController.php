<?php

namespace App\Http\Controllers;

use App\Models\Comunidad;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EtiquetaController extends Controller
{
    public function __invoke(Comunidad $comunidad): Response
    {
        Gate::authorize('view', $comunidad);
        $partes = $comunidad->partes()->with(['propietarios' => fn ($query) => $query->wherePivot('imprimir_etiqueta', true)])->orderBy('codigo')->get();

        return Inertia::render('Comunidades/Etiquetas', ['comunidad' => $comunidad->only('id', 'nombre'), 'partes' => $partes]);
    }
}
