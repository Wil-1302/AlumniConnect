<?php

namespace App\Domain\Catalogos\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RF-31: solo admin_principal puede importar el padrón. El middleware de
 * ruta ya lo exige; esto es una segunda barrera, igual que
 * GuardarOfertaRequest lo hace para el rol administrador.
 */
class ImportarPadronRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->esAdminPrincipal() ?? false;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Seleccione un archivo para importar.',
            'archivo.file'      => 'El archivo no es válido.',
            'archivo.mimes'     => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
            'archivo.max'       => 'El archivo no debe superar los 5 MB.',
        ];
    }
}
