<?php

namespace App\Models;

use Database\Factories\AdministracionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Administracion extends Model
{
    /** @use HasFactory<AdministracionFactory> */
    use HasFactory;

    protected $table = 'administraciones';

    protected $fillable = [
        'propietario_user_id', 'nombre', 'slug', 'activa', 'db_host', 'db_port',
        'db_origen', 'db_usuario', 'db_password', 'sync_estado', 'sync_started_at',
        'sync_finished_at', 'sync_resultado', 'sync_error',
    ];

    protected $hidden = ['db_password'];

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'db_password' => 'encrypted',
            'sync_started_at' => 'datetime',
            'sync_finished_at' => 'datetime',
            'sync_resultado' => 'array',
        ];
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'propietario_user_id');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'administracion_user')
            ->withPivot(['rol', 'puede_gestionar_usuarios'])->withTimestamps();
    }

    public function comunidades(): HasMany
    {
        return $this->hasMany(Comunidad::class);
    }

    public function propietarios(): HasMany
    {
        return $this->hasMany(Propietario::class);
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(InvitacionAcceso::class);
    }
}
