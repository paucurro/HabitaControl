<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexComunidadRequest extends FormRequest
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
            'sort' => ['nullable', 'string', Rule::in(['codigo', 'nombre', 'nif', 'direccion', 'poblacion', 'partes_count'])],
            'direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function sortColumn(): string
    {
        $sort = $this->validated('sort');

        return is_string($sort) ? $sort : 'nombre';
    }

    /** @return 'asc'|'desc' */
    public function sortDirection(): string
    {
        $direction = $this->validated('direction');

        return $direction === 'desc' ? 'desc' : 'asc';
    }
}
