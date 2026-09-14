<?php

namespace App\Domain\Egresados\Services;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Egresados\Models\ExperienciaLaboral;
use App\Domain\Egresados\Repositories\EgresadoRepository;

/**
 * Casos de uso CU-02: actualización del perfil profesional del egresado.
 */
class PerfilService
{
    public function __construct(
        private readonly EgresadoRepository $egresados,
    ) {
    }

    public function actualizarDatosPersonales(Egresado $egresado, array $datos): Egresado
    {
        $egresado->update([
            'telefono'       => $datos['telefono'] ?? $egresado->telefono,
            'ciudad'         => $datos['ciudad'] ?? $egresado->ciudad,
            'perfil_visible' => (bool) ($datos['perfil_visible'] ?? $egresado->perfil_visible),
            'actualizado_en' => now(),
        ]);

        return $egresado->refresh();
    }

    /**
     * Registra la situación laboral vigente. La anterior pasa al historial,
     * de modo que el requisito RF-16 pueda mostrar la trayectoria completa.
     */
    public function actualizarSituacionLaboral(Egresado $egresado, array $datos): ExperienciaLaboral
    {
        return $this->egresados->registrarExperiencia($egresado, [
            'situacion_id' => (int) $datos['situacion_id'],
            'rubro_id'     => $datos['rubro_id'] ?? null,
            'empresa'      => $datos['empresa'] ?? null,
            'cargo'        => $datos['cargo'] ?? null,
            'ciudad'       => $datos['ciudad'] ?? null,
            'fecha_inicio' => $datos['fecha_inicio'] ?? null,
        ]);
    }
}
