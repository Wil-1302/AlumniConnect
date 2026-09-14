<?php

namespace App\Domain\Catalogos\Models;

use Illuminate\Database\Eloquent\Model;

class SituacionLaboral extends Model
{
    protected $table = 'situaciones_laborales';
    public $timestamps = false;

    protected $fillable = ['nombre', 'cuenta_como_empleo', 'requiere_detalle_laboral'];

    protected $casts = [
        'cuenta_como_empleo'        => 'boolean',
        'requiere_detalle_laboral'  => 'boolean',
    ];
}
