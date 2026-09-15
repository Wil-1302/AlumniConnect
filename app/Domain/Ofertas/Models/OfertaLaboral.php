<?php

namespace App\Domain\Ofertas\Models;

use App\Domain\Catalogos\Models\Rubro;
use App\Domain\Seguridad\Models\Usuario;
use Database\Factories\OfertaLaboralFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfertaLaboral extends Model
{
    use HasFactory;

    protected $table = 'ofertas_laborales';

    protected $fillable = [
        'rubro_id', 'creada_por', 'titulo', 'empresa', 'descripcion',
        'requisitos', 'modalidad', 'fecha_publicacion', 'fecha_cierre', 'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'fecha_publicacion' => 'date',
        'fecha_cierre' => 'date',
    ];

    /** RN-04: vencida la fecha de cierre, la oferta deja de mostrarse. */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('activa', true)
            ->whereDate('fecha_cierre', '>=', now()->toDateString());
    }

    /**
     * 'vigente', 'cerrada' o 'desactivada', para listados administrativos.
     * Reutiliza la misma condición que scopeVigentes() en vez de duplicarla
     * en la capa de presentación.
     */
    public function getEstadoAttribute(): string
    {
        return match (true) {
            ! $this->activa => 'desactivada',
            $this->fecha_cierre->lt(now()->startOfDay()) => 'cerrada',
            default => 'vigente',
        };
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubro_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creada_por');
    }

    /** El modelo vive en App\Domain\..., fuera del namespace que Laravel adivina por defecto. */
    protected static function newFactory(): OfertaLaboralFactory
    {
        return OfertaLaboralFactory::new();
    }
}
