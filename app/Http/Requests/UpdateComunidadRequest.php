<?php

namespace App\Http\Requests;

use App\Models\Comunidad;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateComunidadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $comunidad = $this->route('comunidad');

        return $comunidad instanceof Comunidad
            && ($this->user()?->can('update', $comunidad) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...(new StoreComunidadRequest)->rules(),
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('comunidades', 'codigo')
                    ->where('administracion_id', $this->route('comunidad')?->administracion_id)
                    ->ignore($this->route('comunidad')),
            ],
            'archivo' => ['prohibited'],
        ];
    }
}
