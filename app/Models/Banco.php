<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banco extends Model
{
    /** @use HasFactory<\Database\Factories\BancoFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'bancos';

    protected $fillable = [
        'nombre',
        'direccion',
        'codigo_postal',
        'poblacion',
        'provincia',
        'telefonos',
        'iban',
        'bic',
        'codigo_interno',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'es_principal' => 'boolean',
        ];
    }

    /** @return BelongsTo<Comunidad, $this> */
    public function comunidad(): BelongsTo
    {
        return $this->belongsTo(Comunidad::class);
    }
}
