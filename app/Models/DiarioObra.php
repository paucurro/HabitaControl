<?php

namespace App\Models;

use Database\Factories\DiarioObraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiarioObra extends Model
{
    /** @use HasFactory<DiarioObraFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'diario_obras';

    protected $fillable = [
        'comunidad_id', 'tipo_obra_id', 'tipo_gasto_id', 'banco_id', 'parte_id', 'proveedor_id',
        'liquidacion_id', 'fecha', 'numero_documento', 'descripcion', 'debe', 'haber',
        'base_imponible', 'porcentaje_iva',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'debe' => 'decimal:4',
            'haber' => 'decimal:4',
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

    /** @return BelongsTo<TipoObra, $this> */
    public function tipoObra(): BelongsTo
    {
        return $this->belongsTo(TipoObra::class);
    }

    /** @return BelongsTo<Banco, $this> */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
}
