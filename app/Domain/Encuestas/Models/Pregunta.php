<?php

namespace App\Domain\Encuestas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pregunta extends Model
{
    public const TIPO_OPCION_MULTIPLE = 'opcion_multiple';
    public const TIPO_ESCALA          = 'escala';

    protected $table = 'preguntas';
    public $timestamps = false;

    protected $fillable = ['encuesta_id', 'enunciado', 'tipo', 'orden', 'es_obligatoria'];

    protected $casts = ['es_obligatoria' => 'boolean'];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(OpcionPregunta::class, 'pregunta_id')->orderBy('orden');
    }
}
