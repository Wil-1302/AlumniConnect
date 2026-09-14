<?php

namespace App\Domain\Encuestas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpcionPregunta extends Model
{
    protected $table = 'opciones_pregunta';
    public $timestamps = false;

    protected $fillable = ['pregunta_id', 'texto', 'valor', 'orden'];

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class, 'pregunta_id');
    }
}
