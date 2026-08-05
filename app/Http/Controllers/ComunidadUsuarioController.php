<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateComunidadUsuarioRequest;
use App\Models\Administracion;
use App\Models\Comunidad;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class ComunidadUsuarioController extends Controller
{
    public function update(UpdateComunidadUsuarioRequest $request, User $usuario, Comunidad $comunidad): RedirectResponse
    {
        $administracionId = $request->user()->managedAdministracionId();
        abort_if($administracionId === null || $comunidad->administracion_id !== $administracionId, 403);

        $administracion = Administracion::findOrFail($administracionId);
        abort_unless($administracion->usuarios()->whereKey($usuario->id)->wherePivot('rol', 'subusuario')->exists(), 404);

        $permisos = $request->validated();
        $permisos['puede_ver'] = $permisos['puede_ver'] || $permisos['puede_gestionar'];

        if (! $permisos['puede_ver']) {
            $comunidad->usuariosAsignados()->detach($usuario);
        } else {
            $comunidad->usuariosAsignados()->syncWithoutDetaching([
                $usuario->id => [...$permisos, 'asignado_por_user_id' => $request->user()->id],
            ]);
        }

        $this->flashSuccess('Permisos actualizados.');

        return back();
    }
}
