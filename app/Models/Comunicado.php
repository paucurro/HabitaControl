<?php

namespace App\Models;

use Database\Factories\ComunicadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comunicado extends Model
{
    /** @use HasFactory<ComunicadoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'comunicados';

    protected $fillable = ['comunidad_id', 'creado_por_user_id', 'asunto', 'contenido', 'estado', 'publicado_at', 'enviado_at'];

    protected function casts(): array
    {
        return ['publicado_at' => 'datetime', 'enviado_at' => 'datetime'];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    /** @return HasMany<ComunicadoDestinatario, $this> */
    public function destinatarios(): HasMany
    {
        return $this->hasMany(ComunicadoDestinatario::class);
    }
}
