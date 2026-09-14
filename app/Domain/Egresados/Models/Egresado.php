<?php

namespace App\Domain\Egresados\Models;

use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Egresado extends Model
{
    protected $table = 'egresados';

    protected $fillable = [
        'usuario_id', 'dni', 'nombres', 'apellidos', 'anio_egreso',
        'grado', 'telefono', 'ciudad', 'perfil_visible',
        'consentimiento_en', 'actualizado_en',
    ];

    protected $casts = [
        'perfil_visible'    => 'boolean',
        'consentimiento_en' => 'datetime',
        'actualizado_en'    => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function experiencias(): HasMany
    {
        return $this->hasMany(ExperienciaLaboral::class, 'egresado_id');
    }

    /** Situación laboral vigente. Solo existe una por egresado. */
    public function experienciaActual(): HasOne
    {
        return $this->hasOne(ExperienciaLaboral::class, 'egresado_id')
                    ->where('es_actual', true);
    }

    public function estudiosPosgrado(): HasMany
    {
        return $this->hasMany(EstudioPosgrado::class, 'egresado_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->apellidos}, {$this->nombres}");
    }
}
