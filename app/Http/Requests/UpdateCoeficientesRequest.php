<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCoeficientesRequest extends FormRequest
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
            'coeficientes' => ['required', 'array'],
            'coeficientes.*.parte_id' => [
                'required', 'integer',
                Rule::exists('partes', 'id')->where(fn ($query) => $query->where('comunidad_id', $this->route('comunidad')?->id)),
            ],
            'coeficientes.*.tipo_gasto_id' => [
                'required', 'integer',
                Rule::exists('tipos_gasto', 'id')->where(fn ($query) => $query->where('comunidad_id', $this->route('comunidad')?->id)),
            ],
            'coeficientes.*.porcentaje' => ['required', 'numeric', 'between:0,100'],
        ];
    }
}
