<?php

namespace App\Http\Requests;

use App\Models\Banco;
use App\Models\Comunidad;
use App\Models\Parte;
use App\Models\Proveedor;
use App\Models\TipoGasto;
use App\Models\TipoObra;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Validator;
use Throwable;

class StoreDiarioApuntesRequest extends FormRequest
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
            'tipo' => ['required', Rule::in(['apuntes', 'especiales', 'obras'])],
            'apuntes' => ['required', 'array', 'min:1', 'max:200'],
            'apuntes.*.fecha' => ['required', 'date'],
            'apuntes.*.numero_documento' => ['nullable', 'string', 'max:50'],
            'apuntes.*.descripcion' => ['required', 'string', 'max:200'],
            'apuntes.*.tipo' => ['nullable', 'string', 'max:30'],
            'apuntes.*.debe' => ['nullable', 'numeric', 'min:0'],
            'apuntes.*.haber' => ['nullable', 'numeric', 'min:0'],
            'apuntes.*.importe' => ['nullable', 'numeric'],
            'apuntes.*.base_imponible' => ['nullable', 'numeric'],
            'apuntes.*.porcentaje_iva' => ['nullable', 'numeric', 'between:0,100'],
            'apuntes.*.parte_id' => ['nullable', 'integer', $this->scopedExists(new Parte, $comunidad)],
            'apuntes.*.tipo_gasto_id' => ['nullable', 'integer', $this->scopedExists(new TipoGasto, $comunidad)],
            'apuntes.*.banco_id' => ['nullable', 'integer', $this->scopedExists(new Banco, $comunidad)],
            'apuntes.*.tipo_obra_id' => [Rule::requiredIf($this->input('tipo') === 'obras'), 'nullable', 'integer', $this->scopedExists(new TipoObra, $comunidad)],
            'apuntes.*.proveedor_id' => [
                'nullable',
                'integer',
                Rule::exists((new Proveedor)->getTable(), 'id')->where(fn ($query) => $query
                    ->where('administracion_id', $comunidad->administracion_id)
                    ->whereNull('deleted_at')),
            ],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ($this->input('apuntes', []) as $index => $apunte) {
                    if ($this->input('tipo') === 'especiales') {
                        if ((float) ($apunte['importe'] ?? 0) === 0.0) {
                            $validator->errors()->add("apuntes.{$index}.importe", 'El importe no puede ser cero.');
                        }

                        continue;
                    }

                    $debe = (float) ($apunte['debe'] ?? 0);
                    $haber = (float) ($apunte['haber'] ?? 0);

                    if ($debe === 0.0 && $haber === 0.0) {
                        $validator->errors()->add("apuntes.{$index}.debe", 'Indica un ingreso o un pago.');
                    }

                    if ($this->input('tipo') === 'obras' && $debe > 0 && $haber > 0) {
                        $validator->errors()->add("apuntes.{$index}.debe", 'Un apunte de obra no puede tener ingreso y pago a la vez.');
                    }
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $input = $this->input('apuntes', []);
        $apuntes = [];

        foreach (is_array($input) ? $input : [] as $apunte) {
            if (! is_array($apunte)) {
                $apuntes[] = [];

                continue;
            }

            foreach (['parte_id', 'tipo_gasto_id', 'banco_id', 'proveedor_id', 'tipo_obra_id'] as $field) {
                $apunte[$field] = filled($apunte[$field] ?? null) ? $apunte[$field] : null;
            }

            foreach (['debe', 'haber', 'importe', 'base_imponible', 'porcentaje_iva'] as $field) {
                $apunte[$field] = $this->normalizeDecimal($apunte[$field] ?? null);
            }

            if (is_string($apunte['fecha'] ?? null) && preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $apunte['fecha'])) {
                try {
                    $apunte['fecha'] = Carbon::createFromFormat('d/m/Y', $apunte['fecha'])->toDateString();
                } catch (Throwable) {
                }
            }

            $apuntes[] = $apunte;
        }

        $this->merge(['apuntes' => $apuntes]);
    }

    private function normalizeDecimal(mixed $value): mixed
    {
        if (! is_string($value) || ! str_contains($value, ',')) {
            return $value === '' ? null : $value;
        }

        return str_replace(',', '.', str_replace('.', '', $value));
    }

    private function scopedExists(Model $model, Comunidad $comunidad): Exists
    {
        return Rule::exists($model->getTable(), 'id')->where(fn ($query) => $query
            ->where('comunidad_id', $comunidad->id)
            ->whereNull('deleted_at'));
    }
}
