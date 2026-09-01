<?php

namespace App\Actions;

use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrearInvitacionSistemaUsuario
{
    /**
     * @param  array{name: string, email: string, tipo: string, administracion_id?: int|null}  $data
     * @return array{invitacion: InvitacionAcceso, token: string}
     */
    public function handle(User $invitadaPor, array $data): array
    {
        if ($invitadaPor->id !== 1) {
            throw new AuthorizationException;
        }

        $token = Str::random(64);
        $administracionId = $data['administracion_id'] ?? Administracion::query()->value('id');

        $invitacion = DB::transaction(function () use ($invitadaPor, $data, $token, $administracionId): InvitacionAcceso {
            $user = User::query()->firstOrNew(['email' => $data['email']]);

            if (! $user->exists) {
                $user->forceFill([
                    'name' => $data['name'],
                    'password' => Str::random(48),
                    'role' => match ($data['tipo']) {
                        'superusuario' => 'superusuario',
                        'administrador' => 'administrador',
                        default => 'autorizado',
                    },
                ])->save();
            }

            InvitacionAcceso::query()
                ->where('email', $data['email'])
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            return InvitacionAcceso::query()->create([
                'administracion_id' => $administracionId,
                'invitada_por_user_id' => $invitadaPor->id,
                'email' => $data['email'],
                'tipo' => $data['tipo'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);
        });

        return ['invitacion' => $invitacion, 'token' => $token];
    }
}
