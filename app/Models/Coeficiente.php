<?php

namespace App\Models;

use Database\Factories\CoeficienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coeficiente extends Model
{
    /** @use HasFactory<CoeficienteFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'coeficientes';

    protected $fillable = ['comunidad_id', 'parte_id', 'tipo_gasto_id', 'porcentaje'];

    protected function casts(): array
    {
        return ['porcentaje' => 'decimal:8'];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return BelongsTo<Parte, $this> */
    public function parte(): BelongsTo
    {
        return $this->belongsTo(Parte::class);
    }

    /** @return BelongsTo<TipoGasto, $this> */
    public function tipoGasto(): BelongsTo
    {
        return $this->belongsTo(TipoGasto::class);
    }
}
