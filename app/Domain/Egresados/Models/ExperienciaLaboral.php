<?php

namespace App\Domain\Egresados\Models;

use App\Domain\Catalogos\Models\Rubro;
use App\Domain\Catalogos\Models\SituacionLaboral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienciaLaboral extends Model
{
    protected $table = 'experiencias_laborales';
    public $timestamps = false;

    protected $fillable = [
        'egresado_id', 'situacion_id', 'rubro_id', 'empresa', 'cargo',
        'ciudad', 'fecha_inicio', 'fecha_fin', 'es_actual',
    ];

    protected $casts = [
        'es_actual'    => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }

    public function situacion(): BelongsTo
    {
        return $this->belongsTo(SituacionLaboral::class, 'situacion_id');
    }

    public function rubro(): BelongsTo
    {
        return $this->belongsTo(Rubro::class, 'rubro_id');
    }
}
