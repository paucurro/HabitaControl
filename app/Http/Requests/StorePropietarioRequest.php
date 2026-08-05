<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePropietarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['tipo' => ['nullable', 'string', 'max:30'], 'nombre' => ['required', 'string', 'max:200'], 'conyuge' => ['nullable', 'string', 'max:100'], 'nif' => ['nullable', 'string', 'max:20'], 'direccion' => ['nullable', 'string', 'max:200'], 'codigo_postal' => ['nullable', 'string', 'max:10'], 'poblacion' => ['nullable', 'string', 'max:100'], 'provincia' => ['nullable', 'string', 'max:100'], 'pais' => ['nullable', 'string', 'max:100'], 'telefono' => ['nullable', 'string', 'max:30'], 'movil' => ['nullable', 'string', 'max:30'], 'emails' => ['nullable', 'string'], 'iban' => ['nullable', 'string', 'max:34'], 'bic' => ['nullable', 'string', 'max:11'], 'domiciliar_recibos' => ['boolean'], 'enviar_email' => ['boolean'], 'observaciones' => ['nullable', 'string']];
    }
}
