<?php

namespace App\Actions;

use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AceptarInvitacionAcceso
{
    public function handle(string $token, string $name, string $password): User
    {
        return DB::transaction(function () use ($token, $name, $password): User {
            $invitacion = InvitacionAcceso::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if ($invitacion === null || ! $invitacion->isPending()) {
                throw ValidationException::withMessages(['token' => 'La invitación no es válida o ha caducado.']);
            }

            $user = User::query()->firstOrNew(['email' => $invitacion->email]);

            if (! $user->exists) {
                $user->forceFill([
                    'name' => $name,
                    'password' => $password,
                    'email_verified_at' => now(),
                ])->save();
            } elseif ($user->email_verified_at === null) {
                $user->forceFill([
                    'name' => $name,
                    'password' => $password,
                    'email_verified_at' => now(),
                ])->save();
            }

            if (in_array($invitacion->tipo, ['superusuario', 'administrador', 'subusuario', 'autorizado'], true)) {
                $user->forceFill([
                    'name' => $name,
                    'role' => match ($invitacion->tipo) {
                        'superusuario' => 'superusuario',
                        'administrador' => 'administrador',
                        default => 'autorizado',
                    },
                ])->save();
            }

            $invitacion->propietario?->update([
                'user_id' => $user->id,
                'acceso_web' => true,
                'acceso_web_activado_at' => now(),
            ]);

            if (in_array($invitacion->tipo, ['administrador', 'subusuario'], true)) {
                $administracion = Administracion::query()->findOrFail($invitacion->administracion_id);
                $administracion->usuarios()->syncWithoutDetaching([
                    $user->id => [
                        'rol' => $invitacion->tipo,
                        'puede_gestionar_usuarios' => $invitacion->tipo === 'administrador',
                    ],
                ]);
            }

            $invitacion->update(['accepted_at' => now()]);

            return $user;
        });
    }
}
