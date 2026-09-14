<?php

namespace App\Domain\Encuestas\Repositories;

use App\Domain\Encuestas\Models\Encuesta;
use App\Domain\Encuestas\Models\OpcionPregunta;
use App\Domain\Encuestas\Models\Pregunta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Acceso a datos del dominio de encuestas para la administración
 * (RF-19, RF-20). El caso de uso de respuesta del egresado (CU-04, RN-06)
 * sigue viviendo en EncuestaService; este repositorio no lo reemplaza.
 */
class EncuestaRepository
{
    public function todas(int $porPagina = 20): LengthAwarePaginator
    {
        return Encuesta::withCount(['preguntas', 'respuestas'])
            ->orderByDesc('fecha_inicio')
            ->paginate($porPagina);
    }

    public function porId(int $id): ?Encuesta
    {
        return Encuesta::with('preguntas.opciones')->find($id);
    }

    /**
     * Crea la encuesta junto con sus preguntas y, para las de opción
     * múltiple, sus opciones. Todo en una sola transacción para que nunca
     * quede una encuesta a medio configurar.
     *
     * @param  array<int, array{enunciado: string, tipo: string, opciones?: array<int, array{texto: string, valor?: int|null}>}>  $preguntas
     */
    public function crear(array $datosEncuesta, array $preguntas): Encuesta
    {
        return DB::transaction(function () use ($datosEncuesta, $preguntas) {
            $encuesta = Encuesta::create($datosEncuesta);

            foreach (array_values($preguntas) as $orden => $datosPregunta) {
                $pregunta = Pregunta::create([
                    'encuesta_id' => $encuesta->id,
                    'enunciado'   => $datosPregunta['enunciado'],
                    'tipo'        => $datosPregunta['tipo'],
                    'orden'       => $orden + 1,
                ]);

                if ($datosPregunta['tipo'] === Pregunta::TIPO_OPCION_MULTIPLE) {
                    foreach (array_values($datosPregunta['opciones'] ?? []) as $ordenOpcion => $datosOpcion) {
                        OpcionPregunta::create([
                            'pregunta_id' => $pregunta->id,
                            'texto'       => $datosOpcion['texto'],
                            'valor'       => $datosOpcion['valor'] ?? null,
                            'orden'       => $ordenOpcion + 1,
                        ]);
                    }
                }
            }

            return $encuesta->load('preguntas.opciones');
        });
    }

    public function activar(int $id): Encuesta
    {
        $encuesta = Encuesta::findOrFail($id);
        $encuesta->update(['activa' => true]);

        return $encuesta->refresh();
    }

    public function desactivar(int $id): Encuesta
    {
        $encuesta = Encuesta::findOrFail($id);
        $encuesta->update(['activa' => false]);

        return $encuesta->refresh();
    }

    /**
     * Resultados consolidados por pregunta (RF-20): distribución de
     * respuestas por opción para las de opción múltiple, y distribución
     * más promedio para las de escala.
     */
    public function resultados(Encuesta $encuesta): Collection
    {
        $preguntaIds = $encuesta->preguntas->pluck('id');

        $conteos = DB::table('detalle_respuestas')
            ->whereIn('pregunta_id', $preguntaIds)
            ->select('pregunta_id', 'opcion_id', 'valor_escala', DB::raw('COUNT(*) as total'))
            ->groupBy('pregunta_id', 'opcion_id', 'valor_escala')
            ->get()
            ->groupBy('pregunta_id');

        return $encuesta->preguntas->map(function (Pregunta $pregunta) use ($conteos) {
            $filas = $conteos->get($pregunta->id, collect());
            $total = (int) $filas->sum('total');

            if ($pregunta->tipo === Pregunta::TIPO_ESCALA) {
                $distribucion = $filas->mapWithKeys(fn ($fila) => [(int) $fila->valor_escala => (int) $fila->total]);
                $sumaPonderada = $filas->sum(fn ($fila) => $fila->valor_escala * $fila->total);

                return (object) [
                    'pregunta'     => $pregunta,
                    'total'        => $total,
                    'distribucion' => $distribucion,
                    'promedio'     => $total > 0 ? round($sumaPonderada / $total, 2) : null,
                ];
            }

            $conteoPorOpcion = $filas->pluck('total', 'opcion_id');
            $distribucion = $pregunta->opciones->mapWithKeys(fn (OpcionPregunta $opcion) => [
                $opcion->texto => (int) $conteoPorOpcion->get($opcion->id, 0),
            ]);

            return (object) [
                'pregunta'     => $pregunta,
                'total'        => $total,
                'distribucion' => $distribucion,
                'promedio'     => null,
            ];
        });
    }
}
