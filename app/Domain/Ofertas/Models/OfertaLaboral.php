<?php

namespace App\Domain\Ofertas\Models;

use App\Domain\Catalogos\Models\Rubro;
use App\Domain\Seguridad\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfertaLaboral extends Model
{
    protected $table = 'ofertas_laborales';

    protected $fillable = [
        'rubro_id', 'creada_por', 'titulo', 'empresa', 'descripcion',
        'requisitos', 'modalidad', 'fecha_publicacion', 'fecha_cierre', 'activa',
    ];

    protected $casts = [
        'activa'            => 'boolean',
        'fecha_publicacion' => 'date',
        'fecha_cierre'      => 'date',
    ];

    /** RN-04: vencida la fecha de cierre, la oferta deja de mostrarse. */
    public function scopeVigentes(Builder $query): Builder
    {
        return $query->where('activa', true)
                     ->whereDate('fecha_cierre', '>=', now()->toDateString());
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubro_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creada_por');
    }
}
