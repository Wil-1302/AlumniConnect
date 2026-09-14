<?php

namespace App\Domain\Egresados\Repositories;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Egresados\Models\ExperienciaLaboral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Única puerta de acceso a los datos del dominio de egresados.
 *
 * Ninguna otra clase consulta estas tablas directamente (E-08 numeral 2.2).
 */
class EgresadoRepository
{
    public function porDni(string $dni): ?Egresado
    {
        return Egresado::where('dni', $dni)->first();
    }

    public function porUsuario(int $usuarioId): ?Egresado
    {
        return Egresado::where('usuario_id', $usuarioId)->first();
    }

    public function existeDni(string $dni): bool
    {
        return Egresado::where('dni', $dni)->exists();
    }

    public function crear(array $datos): Egresado
    {
        return Egresado::create($datos);
    }

    /**
     * Registra una nueva situación laboral y marca la anterior como histórica.
     * Se ejecuta en transacción para que nunca existan dos vigentes.
     */
    public function registrarExperiencia(Egresado $egresado, array $datos): ExperienciaLaboral
    {
        return DB::transaction(function () use ($egresado, $datos) {
            ExperienciaLaboral::where('egresado_id', $egresado->id)
                ->where('es_actual', true)
                ->update(['es_actual' => false, 'fecha_fin' => now()->toDateString()]);

            $experiencia = ExperienciaLaboral::create(
                $datos + ['egresado_id' => $egresado->id, 'es_actual' => true]
            );

            $egresado->update(['actualizado_en' => now()]);

            return $experiencia;
        });
    }

    /** Directorio con filtros combinables (RF-15). */
    public function directorio(array $filtros, int $porPagina = 20): LengthAwarePaginator
    {
        $consulta = Egresado::query()
            ->with(['experienciaActual.situacion', 'experienciaActual.rubro'])
            ->where('perfil_visible', true);

        if (! empty($filtros['anio_egreso'])) {
            $consulta->where('anio_egreso', $filtros['anio_egreso']);
        }

        if (! empty($filtros['situacion_id'])) {
            $consulta->whereHas('experienciaActual', fn ($q) =>
                $q->where('situacion_id', $filtros['situacion_id'])
            );
        }

        if (! empty($filtros['rubro_id'])) {
            $consulta->whereHas('experienciaActual', fn ($q) =>
                $q->where('rubro_id', $filtros['rubro_id'])
            );
        }

        return $consulta->orderBy('apellidos')->paginate($porPagina);
    }
}
