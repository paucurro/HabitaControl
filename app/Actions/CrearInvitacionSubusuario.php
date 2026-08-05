<?php

namespace App\Actions;

use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CrearInvitacionSubusuario
{
    /** @return array{invitacion: InvitacionAcceso, token: string} */
    public function handle(Administracion $administracion, User $invitadaPor, string $email): array
    {
        if (! $invitadaPor->isAdministracionManager($administracion->id) && $invitadaPor->role !== 'superusuario') {
            throw new AuthorizationException;
        }

        $token = Str::random(64);
        $invitacion = DB::transaction(function () use ($administracion, $invitadaPor, $email, $token): InvitacionAcceso {
            InvitacionAcceso::query()->whereBelongsTo($administracion)->where('email', $email)
                ->where('tipo', 'subusuario')->whereNull('accepted_at')->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            return InvitacionAcceso::create([
                'administracion_id' => $administracion->id,
                'invitada_por_user_id' => $invitadaPor->id,
                'email' => $email,
                'tipo' => 'subusuario',
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);
        });

        return ['invitacion' => $invitacion, 'token' => $token];
    }
}
