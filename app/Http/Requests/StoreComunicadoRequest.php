<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreComunicadoRequest extends FormRequest
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
        return ['comunidad_id' => ['required', 'exists:comunidades,id'], 'asunto' => ['required', 'string', 'max:200'], 'contenido' => ['required', 'string'], 'propietario_ids' => ['required', 'array', 'min:1'], 'propietario_ids.*' => ['integer', 'exists:propietarios,id']];
    }
}
