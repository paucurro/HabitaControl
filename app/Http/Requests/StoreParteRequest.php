<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreParteRequest extends FormRequest
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
        return [
            'comunidad_id' => ['required', 'exists:comunidades,id'],
            'tipo_deposito_id' => ['nullable', 'exists:tipos_deposito,id'],
            'codigo' => ['required', 'string', 'max:20'],
            'descripcion' => ['nullable', 'string', 'max:200'],
            'deposito' => ['nullable', 'numeric'],
            'coeficiente_general' => ['nullable', 'numeric'],
            'orden' => ['nullable', 'string', 'max:200'],
            'tomo' => ['nullable', 'string', 'max:200'],
            'libro' => ['nullable', 'string', 'max:200'],
            'folio' => ['nullable', 'string', 'max:200'],
            'finca' => ['nullable', 'string', 'max:200'],
            'observaciones' => ['nullable', 'string'],
            'propietario_ids' => ['array'],
            'propietario_ids.*' => ['integer', 'exists:propietarios,id'],
        ];
    }
}
