<?php

namespace App\Domain\Reportes\Services;

use App\Domain\Seguridad\Services\AuditoriaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Casos de uso CU-05 y tablero de indicadores (RF-14, RF-23, RF-26).
 *
 * Consulta la vista v_situacion_actual_egresado para no duplicar en el código
 * la lógica de determinación de la situación laboral vigente.
 */
class ReporteEgresadosService
{
    public function __construct(
        private readonly AuditoriaService $auditoria,
    ) {
    }

    /** Indicadores del tablero administrativo (RF-14). */
    public function indicadores(): array
    {
        $total = DB::table('egresados')->count();

        $empleados = DB::table('v_situacion_actual_egresado')
            ->where('cuenta_como_empleo', true)
            ->count();

        return [
            'total_registrados'   => $total,
            'empleados'           => $empleados,
            'tasa_insercion'      => $total > 0 ? round($empleados / $total * 100, 1) : 0.0,
            'por_rubro'           => $this->distribucionPorRubro(),
            'por_promocion'       => $this->distribucionPorPromocion(),
        ];
    }

    public function distribucionPorRubro(): Collection
    {
        return DB::table('v_situacion_actual_egresado')
            ->selectRaw('COALESCE(rubro, \'Sin registrar\') AS rubro, COUNT(*) AS total')
            ->groupBy('rubro')
            ->orderByDesc('total')
            ->get();
    }

    /** RF-26: tasa de inserción desagregada por año de egreso. */
    public function distribucionPorPromocion(): Collection
    {
        return DB::table('v_situacion_actual_egresado')
            ->selectRaw('anio_egreso, COUNT(*) AS total,
                         SUM(cuenta_como_empleo = 1) AS empleados')
            ->groupBy('anio_egreso')
            ->orderBy('anio_egreso')
            ->get();
    }

    /** RF-23: reporte consolidado con filtros. RN-09: queda auditado. */
    public function consolidado(array $filtros, int $usuarioId): Collection
    {
        $consulta = DB::table('v_situacion_actual_egresado');

        if (! empty($filtros['anio_egreso'])) {
            $consulta->where('anio_egreso', $filtros['anio_egreso']);
        }

        if (! empty($filtros['rubro'])) {
            $consulta->where('rubro', $filtros['rubro']);
        }

        $this->auditoria->registrar($usuarioId, 'exportar_reporte', 'egresados');

        return $consulta->orderBy('apellidos')->get();
    }
}
