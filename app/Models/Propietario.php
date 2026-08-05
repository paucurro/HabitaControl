<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    /** @use HasFactory<\Database\Factories\PropietarioFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $table = 'propietarios';

    protected $fillable = ['administracion_id', 'tipo', 'nombre', 'conyuge', 'nif', 'direccion', 'codigo_postal', 'poblacion', 'provincia', 'pais', 'telefono', 'telefono_trabajo', 'movil', 'emails', 'iban', 'bic', 'domiciliar_recibos', 'enviar_email', 'acceso_web', 'observaciones'];

    protected function casts(): array
    {
        return [
            'domiciliar_recibos' => 'boolean',
            'enviar_email' => 'boolean',
            'acceso_web' => 'boolean',
            'acceso_web_activado_at' => 'datetime',
        ];
    }

    public function administracion(): BelongsTo
    {
        return $this->belongsTo(Administracion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsToMany<Parte, $this> */
    public function partes(): BelongsToMany
    {
        return $this->belongsToMany(Parte::class, 'parte_propietario')->withPivot(['imprimir_etiqueta', 'imprimir_liquidacion', 'desde', 'hasta'])->withTimestamps();
    }

    public function emailPrincipal(): ?string
    {
        $email = preg_split('/[,;\s]+/', (string) $this->emails, 2)[0] ?? null;

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /** @param Builder<Propietario> $query */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->role === 'superusuario') {
            $administracionId = $user->selectedAdministracionId();
            $administracionId === null
                ? $query->whereRaw('1 = 0')
                : $query->where('administracion_id', $administracionId);

            return;
        }

        $query->where(function (Builder $query) use ($user): void {
            $query->where('user_id', $user->id)
                ->orWhereHas('administracion', fn (Builder $query) => $query
                    ->where('propietario_user_id', $user->id)
                    ->orWhereHas('usuarios', fn (Builder $query) => $query
                        ->where('users.id', $user->id)
                        ->where('administracion_user.rol', 'administrador')))
                ->orWhereHas('partes.comunidad.usuariosAsignados', fn (Builder $query) => $query
                    ->where('users.id', $user->id)
                    ->where('comunidad_user.puede_ver', true));
        });
    }
}
