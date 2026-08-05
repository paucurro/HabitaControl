<?php

namespace App\Policies;

use App\Models\Comunidad;
use App\Models\User;

class ComunidadPolicy
{
    public function before(User $user): ?bool
    {
        return null;
    }

    public function viewAny(User $user): bool
    {
        if ($user->role === 'superusuario') {
            return $user->selectedAdministracionId() !== null;
        }

        return $user->administracionesPropias()->exists()
            || $user->administraciones()->exists()
            || $user->comunidadesAsignadas()->wherePivot('puede_ver', true)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comunidad $comunidad): bool
    {
        return $user->canViewComunidad($comunidad);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role === 'superusuario') {
            return $user->selectedAdministracionId() !== null;
        }

        return $user->administracionesPropias()->exists()
            || $user->administraciones()->wherePivot('rol', 'administrador')->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comunidad $comunidad): bool
    {
        return $user->canManageComunidad($comunidad);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comunidad $comunidad): bool
    {
        return $user->canManageComunidad($comunidad);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comunidad $comunidad): bool
    {
        return $user->canManageComunidad($comunidad);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comunidad $comunidad): bool
    {
        return false;
    }
}
