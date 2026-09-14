<?php

namespace App\Domain\Encuestas\Requests;

use App\Domain\Encuestas\Models\Pregunta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * RF-19: creación de encuestas con preguntas de opción múltiple o escala.
 */
class GuardarEncuestaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo'                    => ['required', 'string', 'max:150'],
            'descripcion'                => ['nullable', 'string'],
            'fecha_inicio'               => ['required', 'date'],
            'fecha_fin'                  => ['required', 'date', 'after_or_equal:fecha_inicio'],

            'preguntas'                  => ['required', 'array', 'min:1'],
            'preguntas.*.enunciado'      => ['required', 'string', 'max:300'],
            'preguntas.*.tipo'           => ['required', 'in:opcion_multiple,escala'],
            'preguntas.*.opciones'       => ['array'],
            'preguntas.*.opciones.*.texto' => ['nullable', 'string', 'max:200'],
            'preguntas.*.opciones.*.valor' => ['nullable', 'integer'],
        ];
    }

    /**
     * Reglas estructurales que la sintaxis de rules() no expresa bien con
     * comodines cruzados: cada pregunta de opción múltiple necesita al
     * menos dos opciones con texto.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->input('preguntas', []) as $indice => $pregunta) {
                if (($pregunta['tipo'] ?? null) !== Pregunta::TIPO_OPCION_MULTIPLE) {
                    continue;
                }

                $opciones = collect($pregunta['opciones'] ?? [])
                    ->filter(fn ($opcion) => trim($opcion['texto'] ?? '') !== '');

                if ($opciones->count() < 2) {
                    $validator->errors()->add(
                        "preguntas.$indice.opciones",
                        'Las preguntas de opción múltiple necesitan al menos dos opciones con texto.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'preguntas.required'      => 'Agregue al menos una pregunta.',
            'preguntas.*.enunciado.required' => 'Toda pregunta necesita un enunciado.',
            'fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ];
    }
}
