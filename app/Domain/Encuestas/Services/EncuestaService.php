<?php

namespace App\Domain\Encuestas\Services;

use App\Domain\Egresados\Models\Egresado;
use App\Domain\Encuestas\Models\DetalleRespuesta;
use App\Domain\Encuestas\Models\Encuesta;
use App\Domain\Encuestas\Models\Pregunta;
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
     * El Request de presentación (ResponderEncuestaRequest) solo valida
     * formato: que "respuestas" sea un arreglo y que sus valores tengan
     * la forma correcta. Verificar que esas respuestas correspondan
     * exactamente a las preguntas reales de esta encuesta —ni de más ni
     * de menos, con el tipo de valor que cada una exige— es una regla de
     * negocio, y por eso vive aquí, no en la capa de presentación.
     *
     * @param  array<int, array{opcion_id?: int, valor_escala?: int}>  $respuestas
     *         Indexado por identificador de pregunta.
     */
    public function responder(Encuesta $encuesta, Egresado $egresado, array $respuestas): RespuestaEncuesta
    {
        $this->validarNoRespondida($encuesta, $egresado);
        $this->validarPreguntas($encuesta, $respuestas);

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

    /**
     * Valida que "respuestas" tenga exactamente una entrada por cada
     * pregunta de la encuesta (ni faltantes ni ajenas a ella) y que cada
     * entrada tenga el dato que su tipo de pregunta exige.
     */
    private function validarPreguntas(Encuesta $encuesta, array $respuestas): void
    {
        $encuesta->loadMissing('preguntas.opciones');

        $idsPreguntas = $encuesta->preguntas->pluck('id');
        $idsRespondidos = collect(array_keys($respuestas))->map(fn ($id) => (int) $id);

        if ($idsRespondidos->diff($idsPreguntas)->isNotEmpty()) {
            throw new ReglaNegocioException(
                'La respuesta incluye una pregunta que no pertenece a esta encuesta.'
            );
        }

        if ($idsPreguntas->diff($idsRespondidos)->isNotEmpty()) {
            throw new ReglaNegocioException(
                'Debe responder todas las preguntas antes de enviar la encuesta.'
            );
        }

        foreach ($encuesta->preguntas as $pregunta) {
            $valor = $respuestas[$pregunta->id] ?? [];

            if ($pregunta->tipo === Pregunta::TIPO_OPCION_MULTIPLE) {
                $opcionId = $valor['opcion_id'] ?? null;

                if ($opcionId === null || ! $pregunta->opciones->contains('id', (int) $opcionId)) {
                    throw new ReglaNegocioException(
                        'Seleccione una opción válida para cada pregunta de opción múltiple.'
                    );
                }
            } else {
                $valorEscala = $valor['valor_escala'] ?? null;

                if (! is_numeric($valorEscala) || (int) $valorEscala < 1 || (int) $valorEscala > 5) {
                    throw new ReglaNegocioException(
                        'Las preguntas de escala deben responderse con un valor entre 1 y 5.'
                    );
                }
            }
        }
    }
}
