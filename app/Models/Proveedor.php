<?php

namespace App\Models;

use Database\Factories\ProveedorFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('proveedores')]
class Proveedor extends Model
{
    /** @use HasFactory<ProveedorFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['administracion_id', 'nombre', 'nif', 'direccion', 'direccion_adicional', 'telefono', 'contacto', 'email'];

    public function administracion(): BelongsTo
    {
        return $this->belongsTo(Administracion::class);
    }
}
