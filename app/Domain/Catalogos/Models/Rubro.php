<?php

namespace App\Domain\Catalogos\Models;

use App\Domain\Ofertas\Models\OfertaLaboral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rubro extends Model
{
    protected $table = 'rubros';
    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion'];

    public function ofertas(): HasMany
    {
        return $this->hasMany(OfertaLaboral::class, 'rubro_id');
    }
}
