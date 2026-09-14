<?php

namespace App\Domain\Encuestas\Models;

use App\Domain\Egresados\Models\Egresado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RespuestaEncuesta extends Model
{
    protected $table = 'respuestas_encuesta';
    public $timestamps = false;

    protected $fillable = ['encuesta_id', 'egresado_id'];

    protected $casts = ['respondida_en' => 'datetime'];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class, 'encuesta_id');
    }

    public function egresado(): BelongsTo
    {
        return $this->belongsTo(Egresado::class, 'egresado_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRespuesta::class, 'respuesta_id');
    }
}
