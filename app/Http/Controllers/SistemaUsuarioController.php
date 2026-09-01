<?php

namespace App\Http\Controllers;

use App\Actions\CrearInvitacionSistemaUsuario;
use App\Http\Requests\StoreSistemaUsuarioRequest;
use App\Http\Requests\UpdateSistemaUsuarioRequest;
use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\User;
use App\Notifications\InvitacionAccesoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class SistemaUsuarioController extends Controller
{
    public function index(): Response
    {
        abort_unless(auth()->id() === 1, 403);

        return Inertia::render('Sistema/Usuarios', [
            'usuarios' => User::query()
                ->with(['administraciones:id,nombre', 'administracionesPropias:id,nombre,propietario_user_id'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'email_verified_at']),
            'administraciones' => Administracion::query()->orderBy('nombre')->get(['id', 'nombre']),
            'invitaciones' => InvitacionAcceso::query()
                ->with('administracion:id,nombre')
                ->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())
                ->latest()->get(['id', 'administracion_id', 'email', 'tipo', 'expires_at']),
        ]);
    }

    public function store(
        StoreSistemaUsuarioRequest $request,
        CrearInvitacionSistemaUsuario $crearInvitacion,
    ): RedirectResponse {
        $resultado = $crearInvitacion->handle($request->user(), [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'tipo' => $request->string('tipo')->toString(),
            'administracion_id' => $request->filled('administracion_id')
                ? $request->integer('administracion_id')
                : null,
        ]);

        Notification::route('mail', $resultado['invitacion']->email)
            ->notify((new InvitacionAccesoNotification($resultado['invitacion'], $resultado['token']))->afterCommit());
        $this->flashSuccess('Invitación enviada.');

        return back();
    }

    public function update(UpdateSistemaUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $data = $request->validated();
        $usuario->forceFill(['role' => $data['role']])->save();
        $usuario->administraciones()->detach();

        if ($data['tipo_acceso'] !== 'ninguno') {
            $usuario->administraciones()->attach($data['administracion_id'], [
                'rol' => $data['tipo_acceso'],
                'puede_gestionar_usuarios' => $data['tipo_acceso'] === 'administrador',
            ]);
        }

        $this->flashSuccess('Usuario actualizado.');

        return back();
    }
}
