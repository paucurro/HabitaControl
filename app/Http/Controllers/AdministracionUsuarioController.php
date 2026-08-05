<?php

namespace App\Http\Controllers;

use App\Actions\CrearInvitacionSubusuario;
use App\Http\Requests\StoreSubusuarioRequest;
use App\Models\Administracion;
use App\Models\User;
use App\Notifications\InvitacionAccesoNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class AdministracionUsuarioController extends Controller
{
    public function index(): Response
    {
        $administracion = $this->administracionGestionada();

        return Inertia::render('Administracion/Usuarios', [
            'administracion' => $administracion->only(['id', 'nombre']),
            'usuarios' => $administracion->usuarios()->wherePivot('rol', 'subusuario')
                ->with(['comunidadesAsignadas' => fn ($query) => $query->where('administracion_id', $administracion->id)])
                ->orderBy('name')->get(['users.id', 'name', 'email']),
            'comunidades' => $administracion->comunidades()->orderBy('nombre')->get(['id', 'nombre']),
            'invitaciones' => $administracion->invitaciones()->where('tipo', 'subusuario')
                ->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())
                ->latest()->get(['id', 'email', 'expires_at']),
        ]);
    }

    public function store(StoreSubusuarioRequest $request, CrearInvitacionSubusuario $crearInvitacion): RedirectResponse
    {
        $resultado = $crearInvitacion->handle(
            $this->administracionGestionada(),
            $request->user(),
            $request->validated('email'),
        );

        Notification::route('mail', $resultado['invitacion']->email)
            ->notify(new InvitacionAccesoNotification($resultado['invitacion'], $resultado['token']));
        $this->flashSuccess('Invitación enviada al subusuario.');

        return back();
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $administracion = $this->administracionGestionada();
        abort_unless($administracion->usuarios()->whereKey($usuario->id)->wherePivot('rol', 'subusuario')->exists(), 404);

        $comunidadIds = $usuario->comunidadesAsignadas()->where('administracion_id', $administracion->id)->pluck('comunidades.id');
        $usuario->comunidadesAsignadas()->detach($comunidadIds);
        $administracion->usuarios()->detach($usuario);
        $this->flashSuccess('Subusuario eliminado de la administración.');

        return back();
    }

    private function administracionGestionada(): Administracion
    {
        $administracionId = request()->user()->managedAdministracionId();
        abort_if($administracionId === null, 403);

        return Administracion::findOrFail($administracionId);
    }
}
