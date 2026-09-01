<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSincronizacionLegacyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->id === 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'db_host' => ['nullable', 'string', 'max:255'],
            'db_port' => ['nullable', 'integer', 'between:1,65535'],
            'db_origen' => ['required', 'string', 'max:100', 'regex:/\\A[a-zA-Z0-9_]+\\z/'],
            'db_usuario' => ['nullable', 'string', 'max:100'],
            'db_password' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
