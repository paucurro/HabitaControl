<?php

namespace App\Models;

use Database\Factories\DiarioApunteEspecialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiarioApunteEspecial extends Model
{
    /** @use HasFactory<DiarioApunteEspecialFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'diario_apuntes_especiales';

    protected $fillable = [
        'comunidad_id', 'tipo_gasto_id', 'parte_id', 'proveedor_id', 'liquidacion_id', 'tipo',
        'fecha', 'descripcion', 'importe', 'base_imponible', 'porcentaje_iva',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'importe' => 'decimal:4',
            'base_imponible' => 'decimal:4',
            'porcentaje_iva' => 'decimal:4',
        ];
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

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
