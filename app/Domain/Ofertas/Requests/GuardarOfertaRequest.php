<?php

namespace App\Domain\Ofertas\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdministrador() ?? false;
    }

    public function rules(): array
    {
        return [
            'titulo'       => ['required', 'string', 'max:150'],
            'empresa'      => ['required', 'string', 'max:150'],
            'descripcion'  => ['required', 'string'],
            'requisitos'   => ['nullable', 'string'],
            'rubro_id'     => ['nullable', 'integer', 'exists:rubros,id'],
            'modalidad'    => ['required', 'in:presencial,remoto,hibrido'],
            'fecha_cierre' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_cierre.after_or_equal' => 'La fecha de cierre no puede ser anterior a hoy.',
        ];
    }
}
