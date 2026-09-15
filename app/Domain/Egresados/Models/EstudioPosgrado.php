<?php

namespace App\Domain\Egresados\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstudioPosgrado extends Model
{
    protected $table = 'estudios_posgrado';
    public $timestamps = false;

    protected $fillable = [
        'egresado_id', 'grado', 'nombre_programa', 'institucion', 'anio_inicio', 'anio_fin', 'en_curso',
    ];

    protected $casts = ['en_curso' => 'boolean'];

    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }
}
