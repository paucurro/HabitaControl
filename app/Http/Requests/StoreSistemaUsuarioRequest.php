<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSistemaUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->id === 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'tipo' => ['required', Rule::in(['superusuario', 'administrador', 'subusuario', 'autorizado'])],
            'administracion_id' => [
                Rule::requiredIf(fn (): bool => $this->input('tipo') !== 'autorizado'),
                'nullable',
                'integer',
                'exists:administraciones,id',
            ],
        ];
    }
}
