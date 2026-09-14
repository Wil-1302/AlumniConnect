<?php

namespace App\Domain\Egresados\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RNF-07: validación en la frontera. Los datos no alcanzan la capa de
 * negocio sin haber sido verificados en formato y obligatoriedad.
 *
 * RNF-01: solo cinco campos obligatorios, para no exceder los tres minutos
 * de registro y contener el riesgo R-01.
 */
class RegistrarEgresadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni'            => ['required', 'digits:8'],
            'email'          => ['required', 'email:rfc', 'max:150', 'unique:usuarios,email'],
            'password'       => ['required', 'string', 'min:8', 'confirmed'],
            'consentimiento' => ['accepted'],
            'telefono'       => ['nullable', 'string', 'max:20'],
            'ciudad'         => ['nullable', 'string', 'max:80'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.required'            => 'Ingrese su documento de identidad.',
            'dni.digits'              => 'El documento de identidad debe tener 8 dígitos.',
            'email.unique'            => 'Este correo ya se encuentra registrado.',
            'password.min'            => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'      => 'Las contraseñas no coinciden.',
            'consentimiento.accepted' => 'Debe aceptar la política de tratamiento de datos.',
        ];
    }
}
