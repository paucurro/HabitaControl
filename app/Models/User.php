<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * @return HasMany<SocialAccount, $this>
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    /** @return HasMany<Comunidad, $this> */
    public function comunidades(): HasMany
    {
        return $this->hasMany(Comunidad::class);
    }

    public function administracionesPropias(): HasMany
    {
        return $this->hasMany(Administracion::class, 'propietario_user_id');
    }

    public function administraciones(): BelongsToMany
    {
        return $this->belongsToMany(Administracion::class, 'administracion_user')
            ->withPivot(['rol', 'puede_gestionar_usuarios'])->withTimestamps();
    }

    public function comunidadesAsignadas(): BelongsToMany
    {
        return $this->belongsToMany(Comunidad::class, 'comunidad_user')
            ->withPivot(['puede_ver', 'puede_gestionar', 'asignado_por_user_id'])->withTimestamps();
    }

    public function perfilesPropietario(): HasMany
    {
        return $this->hasMany(Propietario::class);
    }

    public function canViewComunidad(Comunidad $comunidad): bool
    {
        if ($this->role === 'superusuario') {
            return $this->selectedAdministracionId() === $comunidad->administracion_id;
        }

        if ($this->isAdministracionManager($comunidad->administracion_id)) {
            return true;
        }

        return $this->comunidadesAsignadas()->whereKey($comunidad)->wherePivot('puede_ver', true)->exists();
    }

    public function canManageComunidad(Comunidad $comunidad): bool
    {
        if ($this->role === 'superusuario') {
            return $this->selectedAdministracionId() === $comunidad->administracion_id;
        }

        if ($this->isAdministracionManager($comunidad->administracion_id)) {
            return true;
        }

        return $this->comunidadesAsignadas()->whereKey($comunidad)->wherePivot('puede_gestionar', true)->exists();
    }

    public function isAdministracionManager(?int $administracionId): bool
    {
        if ($administracionId === null) {
            return false;
        }

        if ($this->role === 'superusuario') {
            return $this->selectedAdministracionId() === $administracionId;
        }

        return $this->administracionesPropias()->whereKey($administracionId)->exists()
            || $this->administraciones()->whereKey($administracionId)->wherePivot('rol', 'administrador')->exists();
    }

    public function managedAdministracionId(): ?int
    {
        if ($this->role === 'superusuario') {
            return $this->selectedAdministracionId();
        }

        return $this->administracionesPropias()->value('id')
            ?? $this->administraciones()->wherePivot('rol', 'administrador')->value('administraciones.id');
    }

    public function selectedAdministracionId(): ?int
    {
        if ($this->role !== 'superusuario') {
            return $this->managedAdministracionId();
        }

        $administracionId = session('selected_administracion_id');

        return is_numeric($administracionId) ? (int) $administracionId : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
