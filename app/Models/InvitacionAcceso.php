<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitacionAcceso extends Model
{
    protected $table = 'invitaciones_acceso';

    protected $fillable = [
        'administracion_id', 'invitada_por_user_id', 'propietario_id', 'email',
        'tipo', 'token_hash', 'expires_at', 'accepted_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function administracion(): BelongsTo
    {
        return $this->belongsTo(Administracion::class);
    }

    public function invitadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitada_por_user_id');
    }

    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->revoked_at === null && $this->expires_at->isFuture();
    }
}
