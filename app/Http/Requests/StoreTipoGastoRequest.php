<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTipoGastoRequest extends FormRequest
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
            'codigo' => [
                'required', 'string', 'max:20',
                Rule::unique('tipos_gasto', 'codigo')->where(fn ($query) => $query->where('comunidad_id', $this->route('comunidad')?->id)),
            ],
            'descripcion' => ['required', 'string', 'max:200'],
            'notas' => ['nullable', 'string'],
            'excluir_de_liquidacion' => ['boolean'],
        ];
    }
}
