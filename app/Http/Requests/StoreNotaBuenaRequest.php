<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreNotaBuenaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'personal_id' => ['required', 'integer', 'exists:personal,id'],
            'jefe_otorga_id' => ['required', 'integer', 'exists:personal,id', 'different:personal_id'],
            'tipo_nota' => ['required', 'string', Rule::in(['asistencia', 'general'])],
            'fecha_expedicion' => ['required', 'date_format:Y-m-d'],
            'numero_oficio' => ['nullable', 'string', 'max:100'],
            'cuerpo' => ['required', 'string', 'min:10'],
            'referencia_articulos' => ['nullable', 'string'],
        ];
    }
}