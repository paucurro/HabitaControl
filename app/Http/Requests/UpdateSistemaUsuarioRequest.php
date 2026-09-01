<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSistemaUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $usuario = $this->route('usuario');

        return $this->user()?->id === 1 && $usuario instanceof User && $usuario->id !== 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['superusuario', 'administrador', 'autorizado'])],
            'tipo_acceso' => ['required', Rule::in(['administrador', 'subusuario', 'ninguno'])],
            'administracion_id' => [
                Rule::requiredIf(fn (): bool => $this->input('tipo_acceso') !== 'ninguno'),
                'nullable',
                'integer',
                'exists:administraciones,id',
            ],
        ];
    }
}
