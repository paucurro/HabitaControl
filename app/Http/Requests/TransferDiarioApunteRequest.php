<?php

namespace App\Http\Requests;

use App\Models\Comunidad;
use App\Models\TipoObra;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransferDiarioApunteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $comunidad = $this->route('comunidad');

        return $comunidad instanceof Comunidad && ($this->user()?->can('update', $comunidad) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Comunidad $comunidad */
        $comunidad = $this->route('comunidad');

        return [
            'destino' => ['required', Rule::in(['apuntes', 'especiales', 'obras'])],
            'tipo_obra_id' => [
                Rule::requiredIf($this->input('destino') === 'obras'),
                'nullable',
                'integer',
                Rule::exists((new TipoObra)->getTable(), 'id')->where(fn ($query) => $query
                    ->where('comunidad_id', $comunidad->id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('destino') === $this->route('tipo')) {
                    $validator->errors()->add('destino', 'El diario de destino debe ser distinto al actual.');
                }
            },
        ];
    }
}
