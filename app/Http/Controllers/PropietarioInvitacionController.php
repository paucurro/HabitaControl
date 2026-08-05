<?php

namespace App\Http\Controllers;

use App\Actions\CrearInvitacionAcceso;
use App\Http\Requests\StorePropietarioInvitacionRequest;
use App\Models\Propietario;
use App\Notifications\InvitacionAccesoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class PropietarioInvitacionController extends Controller
{
    public function __invoke(
        StorePropietarioInvitacionRequest $request,
        Propietario $propietario,
        CrearInvitacionAcceso $crearInvitacion,
    ): RedirectResponse {
        $resultado = $crearInvitacion->handle(
            $propietario->administracion,
            $request->user(),
            $propietario,
            $request->validated('email'),
        );

        Notification::route('mail', $resultado['invitacion']->email)
            ->notify(new InvitacionAccesoNotification($resultado['invitacion'], $resultado['token']));
        $this->flashSuccess('Invitación de acceso enviada.');

        return back();
    }
}
