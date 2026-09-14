<?php

namespace App\Domain\Encuestas\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encuesta extends Model
{
    protected $table = 'encuestas';

    protected $fillable = [
        'creada_por', 'titulo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'activa',
    ];

    protected $casts = [
        'activa'       => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function scopeVigentes(Builder $query): Builder
    {
        $hoy = now()->toDateString();

        return $query->where('activa', true)
                     ->whereDate('fecha_inicio', '<=', $hoy)
                     ->whereDate('fecha_fin', '>=', $hoy);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class, 'encuesta_id')->orderBy('orden');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaEncuesta::class, 'encuesta_id');
    }
}
