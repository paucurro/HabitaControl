<?php

namespace App\Models;

use Database\Factories\TipoObraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TipoObra extends Model
{
    /** @use HasFactory<TipoObraFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'tipos_obra';

    protected $fillable = ['comunidad_id', 'codigo', 'descripcion', 'notas'];

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return HasMany<DiarioObra, $this> */
    public function apuntes(): HasMany
    {
        return $this->hasMany(DiarioObra::class);
    }
}
