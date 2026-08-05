<?php

namespace App\Models;

use Database\Factories\ParteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parte extends Model
{
    /** @use HasFactory<ParteFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'partes';

    protected $fillable = ['comunidad_id', 'tipo_deposito_id', 'codigo', 'descripcion', 'deposito', 'coeficiente_general', 'orden', 'tomo', 'libro', 'folio', 'finca', 'observaciones'];

    protected function casts(): array
    {
        return ['deposito' => 'decimal:4', 'coeficiente_general' => 'decimal:8'];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return BelongsTo<TipoDeposito, $this> */
    public function tipoDeposito(): BelongsTo
    {
        return $this->belongsTo(TipoDeposito::class);
    }

    /** @return HasMany<Coeficiente, $this> */
    public function coeficientes(): HasMany
    {
        return $this->hasMany(Coeficiente::class);
    }

    /** @return BelongsToMany<Propietario, $this> */
    public function propietarios(): BelongsToMany
    {
        return $this->belongsToMany(Propietario::class, 'parte_propietario')->withPivot(['imprimir_etiqueta', 'imprimir_liquidacion', 'desde', 'hasta'])->withTimestamps();
    }
}
