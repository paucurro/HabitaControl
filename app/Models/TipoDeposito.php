<?php

namespace App\Models;

use Database\Factories\TipoDepositoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoDeposito extends Model
{
    /** @use HasFactory<TipoDepositoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'tipos_deposito';

    protected $fillable = ['comunidad_id', 'nombre', 'importe', 'excluir_de_liquidacion'];

    protected function casts(): array
    {
        return ['importe' => 'decimal:4', 'excluir_de_liquidacion' => 'boolean'];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return HasMany<Parte, $this> */
    public function partes(): HasMany
    {
        return $this->hasMany(Parte::class);
    }
}
