<?php

namespace App\Models;

use Database\Factories\TipoGastoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoGasto extends Model
{
    /** @use HasFactory<TipoGastoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'tipos_gasto';

    protected $fillable = ['comunidad_id', 'codigo', 'descripcion', 'notas', 'excluir_de_liquidacion'];

    protected function casts(): array
    {
        return ['excluir_de_liquidacion' => 'boolean'];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return HasMany<Coeficiente, $this> */
    public function coeficientes(): HasMany
    {
        return $this->hasMany(Coeficiente::class);
    }
}
