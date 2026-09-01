<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Validator;

class UpdateDiarioApunteRequest extends StoreDiarioApuntesRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'apuntes' => ['required', 'array', 'size:1'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                if ($this->input('tipo') !== $this->route('tipo')) {
                    $validator->errors()->add('tipo', 'El tipo de diario no coincide con el apunte.');
                }
            },
        ];
    }
}
