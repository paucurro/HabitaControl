<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComunicadoDestinatario extends Model
{
    use SoftDeletes;

    protected $table = 'comunicado_destinatarios';

    protected $fillable = ['comunicado_id', 'propietario_id', 'parte_id', 'email', 'estado', 'enviado_at', 'error'];

    protected function casts(): array
    {
        return ['enviado_at' => 'datetime'];
    }

    /** @return BelongsTo<Comunicado, $this> */
    public function comunicado(): BelongsTo
    {
        return $this->belongsTo(Comunicado::class);
    }
}
