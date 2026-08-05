<?php

namespace App\Actions;

use App\Models\Administracion;
use App\Models\InvitacionAcceso;
use App\Models\Propietario;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrearInvitacionAcceso
{
    /** @return array{invitacion: InvitacionAcceso, token: string} */
    public function handle(
        Administracion $administracion,
        User $invitadaPor,
        Propietario $propietario,
        ?string $email = null,
    ): array {
        if (! $invitadaPor->isAdministracionManager($administracion->id) && $invitadaPor->role !== 'superusuario') {
            throw new AuthorizationException;
        }

        if ($propietario->administracion_id !== $administracion->id) {
            throw ValidationException::withMessages(['propietario' => 'El propietario no pertenece a esta administración.']);
        }

        $invitationEmail = $email ?? $propietario->emailPrincipal();

        if (filter_var($invitationEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw ValidationException::withMessages(['email' => 'El propietario no tiene un email válido.']);
        }

        $token = Str::random(64);

        $invitacion = DB::transaction(function () use ($administracion, $invitadaPor, $propietario, $invitationEmail, $token): InvitacionAcceso {
            InvitacionAcceso::query()
                ->whereBelongsTo($administracion)
                ->whereBelongsTo($propietario)
                ->whereNull('accepted_at')
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            return InvitacionAcceso::create([
                'administracion_id' => $administracion->id,
                'invitada_por_user_id' => $invitadaPor->id,
                'propietario_id' => $propietario->id,
                'email' => $invitationEmail,
                'tipo' => 'propietario',
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);
        });

        return ['invitacion' => $invitacion, 'token' => $token];
    }
}
