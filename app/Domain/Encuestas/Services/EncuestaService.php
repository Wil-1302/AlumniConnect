<?php

namespace App\Domain\Encuestas\Services;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Encuestas\Models\DetalleRespuesta;
use App\Domain\Encuestas\Models\Encuesta;
use App\Domain\Encuestas\Models\RespuestaEncuesta;
use App\Shared\Exceptions\ReglaNegocioException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Caso de uso CU-04: responder una encuesta.
 */
class EncuestaService
{
    /** Encuestas vigentes que el egresado aún no ha respondido. */
    public function pendientesPara(Egresado $egresado): Collection
    {
        return Encuesta::vigentes()
            ->whereDoesntHave('respuestas', fn ($q) => $q->where('egresado_id', $egresado->id))
            ->orderBy('fecha_fin')
            ->get();
    }

    /**
     * Registra las respuestas de un egresado.
     *
     * @param  array<int, array{opcion_id?: int, valor_escala?: int}>  $respuestas
     *         Indexado por identificador de pregunta.
     */
    public function responder(Encuesta $encuesta, Egresado $egresado, array $respuestas): RespuestaEncuesta
    {
        $this->validarNoRespondida($encuesta, $egresado);
        $this->validarPreguntasCompletas($encuesta, $respuestas);

        return DB::transaction(function () use ($encuesta, $egresado, $respuestas) {
            $cabecera = RespuestaEncuesta::create([
                'encuesta_id' => $encuesta->id,
                'egresado_id' => $egresado->id,
            ]);

            foreach ($respuestas as $preguntaId => $valor) {
                DetalleRespuesta::create([
                    'respuesta_id' => $cabecera->id,
                    'pregunta_id'  => (int) $preguntaId,
                    'opcion_id'    => $valor['opcion_id'] ?? null,
                    'valor_escala' => $valor['valor_escala'] ?? null,
                ]);
            }

            return $cabecera;
        });
    }

    /** RN-06: un egresado responde cada encuesta una sola vez. */
    private function validarNoRespondida(Encuesta $encuesta, Egresado $egresado): void
    {
        $yaRespondio = RespuestaEncuesta::where('encuesta_id', $encuesta->id)
            ->where('egresado_id', $egresado->id)
            ->exists();

        if ($yaRespondio) {
            throw ReglaNegocioException::regla(
                'RN-06',
                'Usted ya respondió esta encuesta anteriormente.'
            );
        }
    }

    private function validarPreguntasCompletas(Encuesta $encuesta, array $respuestas): void
    {
        $faltantes = $encuesta->preguntas()
            ->pluck('id')
            ->diff(array_keys($respuestas));

        if ($faltantes->isNotEmpty()) {
            throw new ReglaNegocioException(
                'Debe responder todas las preguntas antes de enviar la encuesta.'
            );
        }
    }
}
