<?php

namespace App\Http\Controllers;

use App\Actions\AceptarInvitacionAcceso;
use App\Http\Requests\AceptarInvitacionAccesoRequest;
use App\Models\InvitacionAcceso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvitacionAccesoController extends Controller
{
    public function show(string $token): Response
    {
        $invitacion = $this->invitacionValida($token);

        return Inertia::render('auth/AceptarInvitacion', [
            'token' => $token,
            'email' => $invitacion->email,
            'administracion' => $invitacion->administracion->nombre,
            'tipo' => $invitacion->tipo,
        ]);
    }

    public function store(
        AceptarInvitacionAccesoRequest $request,
        string $token,
        AceptarInvitacionAcceso $aceptarInvitacion,
    ): RedirectResponse {
        $user = $aceptarInvitacion->handle($token, $request->validated('name'), $request->validated('password'));
        Auth::login($user);
        $request->session()->regenerate();

        return to_route('dashboard');
    }

    private function invitacionValida(string $token): InvitacionAcceso
    {
        $invitacion = InvitacionAcceso::query()->with('administracion:id,nombre')
            ->where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_unless($invitacion->isPending(), 410);

        return $invitacion;
    }
}
