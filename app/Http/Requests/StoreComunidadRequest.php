<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComunidadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Comunidad::class) ?? false;
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
                'required',
                'string',
                'max:20',
                Rule::unique('comunidades', 'codigo')
                    ->where('administracion_id', $this->user()?->managedAdministracionId()),
            ],
            'nombre' => ['required', 'string', 'max:200'],
            'nif' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'poblacion' => ['nullable', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'pais' => ['nullable', 'string', 'max:100'],
            'presidente_nombre' => ['nullable', 'string', 'max:100'],
            'presidente_telefono' => ['nullable', 'string', 'max:30'],
            'vicepresidente_nombre' => ['nullable', 'string', 'max:100'],
            'vicepresidente_telefono' => ['nullable', 'string', 'max:30'],
            'aseguradora' => ['nullable', 'string', 'max:100'],
            'poliza_seguro' => ['nullable', 'string', 'max:60'],
            'contacto_seguro' => ['nullable', 'string', 'max:100'],
            'telefono_seguro' => ['nullable', 'string', 'max:30'],
            'fondo_reserva' => ['nullable', 'numeric'],
            'copias_informe' => ['nullable', 'integer', 'min:0'],
            'modelo_impresion' => ['nullable', 'string', 'max:200'],
            'texto_liquidacion' => ['nullable', 'string'],
            'plazo_maximo_pago_dias' => ['nullable', 'integer', 'min:0'],
            'penalizacion' => ['nullable', 'numeric'],
            'ano_construccion' => ['nullable', 'integer', 'min:0', 'max:'.now()->year],
            'iee' => ['nullable', 'string'],
            'imprimir_estado' => ['boolean'],
            'imprimir_nombres_resumen' => ['boolean'],
            'observaciones' => ['nullable', 'string'],
            'bancos' => ['nullable', 'array'],
            'bancos.*' => ['array:nombre,direccion,codigo_postal,poblacion,provincia,telefonos,iban,bic,codigo_interno'],
            'bancos.*.nombre' => ['nullable', 'string', 'max:200'],
            'bancos.*.direccion' => ['nullable', 'string', 'max:200'],
            'bancos.*.codigo_postal' => ['nullable', 'string', 'max:10'],
            'bancos.*.poblacion' => ['nullable', 'string', 'max:100'],
            'bancos.*.provincia' => ['nullable', 'string', 'max:100'],
            'bancos.*.telefonos' => ['nullable', 'string', 'max:200'],
            'bancos.*.iban' => ['nullable', 'string', 'max:34'],
            'bancos.*.bic' => ['nullable', 'string', 'max:11'],
            'bancos.*.codigo_interno' => ['nullable', 'string', 'max:20'],
            'banco_principal' => ['nullable', 'integer', 'min:0'],
            'archivo' => [
                'nullable',
                'file',
                'mimes:csv,txt',
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel',
                'max:10240',
            ],
        ];
    }
}
