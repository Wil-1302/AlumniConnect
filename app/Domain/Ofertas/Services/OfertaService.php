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
    ) {}

    public function listarVigentes(array $filtros = []): LengthAwarePaginator
    {
        return $this->ofertas->vigentes($filtros);
    }

    public function verDetalle(int $ofertaId): OfertaLaboral
    {
        return $this->obtener($ofertaId);
    }

    public function publicar(array $datos, int $usuarioId): OfertaLaboral
    {
        $this->validarFechaCierre($datos['fecha_cierre']);

        return $this->ofertas->crear($datos + [
            'creada_por' => $usuarioId,
            'fecha_publicacion' => now()->toDateString(),
            'activa' => true,
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

    /**
     * Puede dejar la oferta en estado "cerrada" si su fecha_cierre ya pasó
     * (vuelve a mostrarse en la bolsa solo si esa fecha sigue vigente); no
     * se bloquea porque reactivar una oferta vencida no infringe ninguna
     * regla, solo no la hace visible hasta que se edite la fecha.
     */
    public function activar(int $ofertaId): OfertaLaboral
    {
        return $this->ofertas->actualizar($this->obtener($ofertaId), ['activa' => true]);
    }

    /** Elimina la oferta definitivamente (a diferencia de desactivar, no queda histórico). */
    public function eliminar(int $ofertaId): void
    {
        $this->ofertas->eliminar($this->obtener($ofertaId));
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
