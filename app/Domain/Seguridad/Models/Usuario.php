<?php

namespace App\Domain\Seguridad\Models;

use App\Domain\Egresados\Models\Egresado;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable implements AuthenticatableContract
{
    use HasFactory;

    public const ROL_EGRESADO = 'egresado';

    public const ROL_ADMINISTRADOR = 'administrador';

    public const ROL_ADMIN_PRINCIPAL = 'admin_principal';

    protected $table = 'usuarios';

    protected $fillable = ['email', 'password_hash', 'rol', 'activo'];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'activo' => 'boolean',
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

    /** RF-31: solo admin_principal puede importar el padrón; administrador solo lo consulta. */
    public function esAdminPrincipal(): bool
    {
        return $this->rol === self::ROL_ADMIN_PRINCIPAL;
    }

    /**
     * Nombre de la ruta de la zona propia del usuario según su rol.
     * Punto único usado tras el login, en la portada y en la
     * redirección de RedirectIfAuthenticated (ver AppServiceProvider).
     */
    public function rutaPrincipal(): string
    {
        return $this->esAdministrador() ? 'admin.tablero' : 'egresado.perfil';
    }
}
