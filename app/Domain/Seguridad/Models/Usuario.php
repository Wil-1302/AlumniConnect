<?php

namespace App\Domain\Seguridad\Models;

use App\Domain\Egresados\Models\Egresado;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Authenticatable implements AuthenticatableContract
{
    public const ROL_EGRESADO        = 'egresado';
    public const ROL_ADMINISTRADOR   = 'administrador';
    public const ROL_ADMIN_PRINCIPAL = 'admin_principal';

    protected $table = 'usuarios';

    protected $fillable = ['email', 'password_hash', 'rol', 'activo'];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'activo'        => 'boolean',
        'ultimo_acceso' => 'datetime',
    ];

    /** Laravel espera el nombre 'password'; el esquema usa 'password_hash'. */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function egresado(): HasOne
    {
        return $this->hasOne(Egresado::class, 'usuario_id');
    }

    public function esAdministrador(): bool
    {
        return in_array($this->rol, [self::ROL_ADMINISTRADOR, self::ROL_ADMIN_PRINCIPAL], true);
    }
}
