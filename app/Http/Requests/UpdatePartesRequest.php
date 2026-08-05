<?php

namespace App\Http\Requests;

use App\Models\Comunidad;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartesRequest extends FormRequest
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
        $comunidad = $this->routeComunidad();

        return [
            'partes' => ['required', 'array', 'min:1'],
            'partes.*' => ['required', 'array:id,codigo,descripcion,coeficiente_general,propietario_ids'],
            'partes.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('partes', 'id')->where(fn ($query) => $query
                    ->where('comunidad_id', $comunidad?->id)
                    ->whereNull('deleted_at')),
            ],
            'partes.*.codigo' => ['required', 'string', 'max:20', 'distinct:strict'],
            'partes.*.descripcion' => ['nullable', 'string', 'max:200'],
            'partes.*.coeficiente_general' => ['nullable', 'numeric'],
            'partes.*.propietario_ids' => ['present', 'array'],
            'partes.*.propietario_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('propietarios', 'id')->where(fn ($query) => $query
                    ->where('administracion_id', $comunidad?->administracion_id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }

    /**
     * @return list<array{id: int, codigo: string, descripcion: string|null, coeficiente_general: string|null, propietario_ids: list<int>}>
     */
    public function validatedPartes(): array
    {
        $partes = [];

        foreach ($this->safe()->array('partes') as $parte) {
            if (! is_array($parte)) {
                continue;
            }

            $propietarioIds = is_array($parte['propietario_ids'] ?? null)
                ? $parte['propietario_ids']
                : [];

            $partes[] = [
                'id' => (int) ($parte['id'] ?? 0),
                'codigo' => (string) ($parte['codigo'] ?? ''),
                'descripcion' => isset($parte['descripcion']) ? (string) $parte['descripcion'] : null,
                'coeficiente_general' => isset($parte['coeficiente_general']) ? (string) $parte['coeficiente_general'] : null,
                'propietario_ids' => array_values(array_map(static fn (mixed $id): int => (int) $id, $propietarioIds)),
            ];
        }

        return $partes;
    }

    private function routeComunidad(): ?Comunidad
    {
        $comunidad = $this->route('comunidad');

        return $comunidad instanceof Comunidad ? $comunidad : null;
    }
}
