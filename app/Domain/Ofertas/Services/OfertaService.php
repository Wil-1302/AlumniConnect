<?php

namespace App\Domain\Ofertas\Services;

use App\Domain\Ofertas\Models\OfertaLaboral;
use App\Domain\Ofertas\Repositories\OfertaRepository;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Casos de uso CU-03 (publicar oferta) y consulta de la bolsa de trabajo.
 */
class OfertaService
{
    public function __construct(
        private readonly OfertaRepository $ofertas,
    ) {
    }

    public function listarVigentes(array $filtros = []): LengthAwarePaginator
    {
        return $this->ofertas->vigentes($filtros);
    }

    public function publicar(array $datos, int $usuarioId): OfertaLaboral
    {
        $this->validarFechaCierre($datos['fecha_cierre']);

        return $this->ofertas->crear($datos + [
            'creada_por'        => $usuarioId,
            'fecha_publicacion' => now()->toDateString(),
            'activa'            => true,
        ]);
    }

    public function actualizar(int $ofertaId, array $datos): OfertaLaboral
    {
        $oferta = $this->obtener($ofertaId);

        if (isset($datos['fecha_cierre'])) {
            $this->validarFechaCierre($datos['fecha_cierre']);
        }

        return $this->ofertas->actualizar($oferta, $datos);
    }

    /** RF-18: la desactivación conserva la oferta en el histórico. */
    public function desactivar(int $ofertaId): OfertaLaboral
    {
        return $this->ofertas->actualizar($this->obtener($ofertaId), ['activa' => false]);
    }

    private function obtener(int $ofertaId): OfertaLaboral
    {
        $oferta = $this->ofertas->porId($ofertaId);

        if ($oferta === null) {
            throw new ReglaNegocioException('La oferta laboral solicitada no existe.');
        }

        return $oferta;
    }

    /** RN-04: no se admite una oferta cuya fecha de cierre ya venció. */
    private function validarFechaCierre(string $fechaCierre): void
    {
        if ($fechaCierre < now()->toDateString()) {
            throw ReglaNegocioException::regla(
                'RN-04',
                'La fecha de cierre no puede ser anterior a la fecha actual.'
            );
        }
    }
}
