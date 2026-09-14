<?php

namespace App\Domain\Encuestas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRespuesta extends Model
{
    protected $table = 'detalle_respuestas';
    public $timestamps = false;

    protected $fillable = ['respuesta_id', 'pregunta_id', 'opcion_id', 'valor_escala'];

    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(RespuestaEncuesta::class, 'respuesta_id');
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }
}
