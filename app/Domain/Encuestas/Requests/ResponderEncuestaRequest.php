<?php

namespace App\Domain\Encuestas\Requests;

use App\Domain\Encuestas\Models\Pregunta;
use App\Domain\Encuestas\Repositories\EncuestaRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CU-04: las reglas dependen de las preguntas de la encuesta de la ruta,
 * así que se construyen dinámicamente. La consulta para conocerlas se
 * delega en EncuestaRepository (no se hace directo aquí).
 */
class ResponderEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $encuesta = app(EncuestaRepository::class)->porId((int) $this->route('id'));

        if ($encuesta === null) {
            return [];
        }

        $reglas = [];

        foreach ($encuesta->preguntas as $pregunta) {
            if ($pregunta->tipo === Pregunta::TIPO_OPCION_MULTIPLE) {
                $reglas["respuestas.{$pregunta->id}.opcion_id"] = [
                    'required',
                    Rule::exists('opciones_pregunta', 'id')->where('pregunta_id', $pregunta->id),
                ];
            } else {
                $reglas["respuestas.{$pregunta->id}.valor_escala"] = ['required', 'integer', 'between:1,5'];
            }
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'required' => 'Debe responder todas las preguntas antes de enviar la encuesta.',
            '*.exists' => 'Seleccione una opción válida.',
        ];
    }
}
