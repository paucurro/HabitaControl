<?php

namespace App\Http\Controllers;

use App\Http\Requests\SeleccionarAdministracionRequest;
use Illuminate\Http\RedirectResponse;

class AdministracionContextController extends Controller
{
    public function __invoke(SeleccionarAdministracionRequest $request): RedirectResponse
    {
        $administracionId = $request->validated('administracion_id');

        if ($administracionId === null) {
            $request->session()->forget('selected_administracion_id');
        } else {
            $request->session()->put('selected_administracion_id', $administracionId);
        }

        return to_route('dashboard');
    }
}
