<?php

namespace App\Domain\Catalogos\Repositories;

use App\Domain\Catalogos\Models\Rubro;
use App\Domain\Catalogos\Models\SituacionLaboral;
use Illuminate\Support\Collection;

/**
 * Acceso a los catálogos del sistema.
 *
 * Existe para que los controladores no consulten Eloquent directamente:
 * la regla de arquitectura prohíbe el acceso a datos fuera de esta capa,
 * incluso cuando se trata de lecturas simples (E-08 numeral 2.2).
 */
class CatalogoRepository
{
    public function rubros(): Collection
    {
        return Rubro::orderBy('nombre')->get();
    }

    public function situaciones(): Collection
    {
        return SituacionLaboral::orderBy('id')->get();
    }
}
