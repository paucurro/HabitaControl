<?php

namespace App\Models;

use Database\Factories\ComunidadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comunidad extends Model
{
    /** @use HasFactory<ComunidadFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'comunidades';

    protected $fillable = [
        'administracion_id', 'codigo', 'nif', 'nombre', 'direccion', 'codigo_postal', 'poblacion', 'provincia', 'pais',
        'presidente_nombre', 'presidente_telefono', 'vicepresidente_nombre', 'vicepresidente_telefono',
        'aseguradora', 'poliza_seguro', 'contacto_seguro', 'telefono_seguro',
        'fondo_reserva', 'copias_informe', 'modelo_impresion', 'texto_liquidacion', 'plazo_maximo_pago_dias', 'penalizacion',
        'ano_construccion', 'iee', 'imprimir_estado', 'imprimir_nombres_resumen', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fondo_reserva' => 'decimal:4',
            'penalizacion' => 'decimal:4',
            'copias_informe' => 'integer',
            'plazo_maximo_pago_dias' => 'integer',
            'ano_construccion' => 'integer',
            'imprimir_estado' => 'boolean',
            'imprimir_nombres_resumen' => 'boolean',
        ];
    }

    /** @param Builder<Comunidad> $query */
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
            $query->whereHas('administracion', fn (Builder $query) => $query
                ->where('propietario_user_id', $user->id)
                ->orWhereHas('usuarios', fn (Builder $query) => $query
                    ->where('users.id', $user->id)
                    ->where('administracion_user.rol', 'administrador')))
                ->orWhereHas('usuariosAsignados', fn (Builder $query) => $query
                    ->where('users.id', $user->id)
                    ->where('comunidad_user.puede_ver', true));
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function administracion(): BelongsTo
    {
        return $this->belongsTo(Administracion::class);
    }

    public function usuariosAsignados(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comunidad_user')
            ->withPivot(['puede_ver', 'puede_gestionar', 'asignado_por_user_id'])
            ->withTimestamps();
    }

    /** @return HasMany<Parte, $this> */
    public function partes(): HasMany
    {
        return $this->hasMany(Parte::class);
    }

    /** @return HasMany<Comunicado, $this> */
    public function comunicados(): HasMany
    {
        return $this->hasMany(Comunicado::class);
    }

    /** @return HasMany<TipoGasto, $this> */
    public function tiposGasto(): HasMany
    {
        return $this->hasMany(TipoGasto::class);
    }

    /** @return HasMany<TipoDeposito, $this> */
    public function tiposDeposito(): HasMany
    {
        return $this->hasMany(TipoDeposito::class);
    }

    /** @return HasMany<Banco, $this> */
    public function bancos(): HasMany
    {
        return $this->hasMany(Banco::class);
    }
}
