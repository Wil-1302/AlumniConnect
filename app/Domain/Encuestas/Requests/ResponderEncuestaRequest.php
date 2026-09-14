<?php

namespace App\Domain\Encuestas\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CU-04: valida únicamente formato y estructura del arreglo "respuestas".
 * Que esas respuestas correspondan a preguntas reales de la encuesta y
 * traigan el tipo de valor correcto para cada una es una regla de
 * negocio, no de formato, y por eso la verifica EncuestaService, no este
 * Request (E-08 numeral 2.2: la presentación recibe datos, no los busca).
 */
class ResponderEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respuestas'                  => ['required', 'array', 'min:1'],
            'respuestas.*.opcion_id'      => ['nullable', 'integer'],
            'respuestas.*.valor_escala'   => ['nullable', 'integer', 'between:1,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'respuestas.required' => 'Debe responder todas las preguntas antes de enviar la encuesta.',
        ];
    }
}
