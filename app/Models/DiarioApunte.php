<?php

namespace App\Models;

use Database\Factories\DiarioApunteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiarioApunte extends Model
{
    /** @use HasFactory<DiarioApunteFactory> */
    use HasFactory;

    protected $table = 'diario_apuntes';

    protected $fillable = [];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'debe' => 'decimal:4', 'haber' => 'decimal:4'];
    }

    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }

    public function parte(): BelongsTo
    {
        return $this->belongsTo(Parte::class);
    }
}
