<?php

namespace App\Domain\Ofertas\Repositories;

use App\Domain\Ofertas\Models\OfertaLaboral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OfertaRepository
{
    /** Ofertas visibles para el egresado (RF-08, RF-09, RN-04, RN-05). */
    public function vigentes(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        $consulta = OfertaLaboral::vigentes()->with('rubro');

        if (! empty($filtros['rubro_id'])) {
            $consulta->where('rubro_id', $filtros['rubro_id']);
        }

        if (! empty($filtros['modalidad'])) {
            $consulta->where('modalidad', $filtros['modalidad']);
        }

        return $consulta->orderByDesc('fecha_publicacion')->paginate($porPagina);
    }

    /** Listado administrativo: incluye ofertas cerradas y desactivadas. */
    public function todas(int $porPagina = 20): LengthAwarePaginator
    {
        return OfertaLaboral::with('rubro')
            ->orderByDesc('fecha_publicacion')
            ->paginate($porPagina);
    }

    public function porId(int $id): ?OfertaLaboral
    {
        return OfertaLaboral::find($id);
    }

    public function crear(array $datos): OfertaLaboral
    {
        return OfertaLaboral::create($datos);
    }

    public function actualizar(OfertaLaboral $oferta, array $datos): OfertaLaboral
    {
        $oferta->update($datos);

        return $oferta->refresh();
    }
}
