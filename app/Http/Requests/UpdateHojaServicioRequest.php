<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateHojaServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Permitir si está autenticado. Podrías añadir lógica de permisos más fina aquí.
     * Por ejemplo, verificar si el usuario puede editar ESTA hoja en particular.
     */
    public function authorize(): bool
    {
        // Por ahora, simple: solo usuarios logueados
        return Auth::check();
        // Ejemplo más avanzado: return Auth::user()->can('update', $this->route('hoja'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Usamos reglas similares a la creación, ajusta si es necesario.
        // Por ejemplo, 'personal_id' podría no ser editable, en cuyo caso lo quitamos de aquí.
        // Si un campo es único y editable, la regla unique necesita ignorar el registro actual:
        // 'campo_unico' => ['required', 'string', 'unique:tabla,campo,' . $this->route('hoja')->id],
        return [
            // 'personal_id' => ['sometimes', 'required', 'integer', 'exists:personal,id'], // ¿Permites cambiar el personal de una hoja? Si no, quita esta regla.
            'periodo_id' => ['required', 'integer', 'exists:periodos_disponibles,id'],
            'fecha_expedicion' => ['required', 'date_format:Y-m-d'],
            'observaciones' => ['nullable', 'string', 'max:5000'],
            // Usar 'personal' o 'personals' según tu tabla:
            'jefe_inmediato_id' => ['nullable', 'integer', 'different:personal_id', 'exists:personal,id'],
            'incidencias' => ['nullable', 'array', 'max:20'],
            'incidencias.*.tipo' => ['required_with:incidencias', 'string', 'size:2', 'in:NB,FE,NM,EX,AM,SU'],
            'incidencias.*.fecha' => ['required_with:incidencias', 'date_format:Y-m-d'],
            'incidencias.*.descripcion' => ['required_with:incidencias', 'string', 'min:1', 'max:1000'],
        ];
    }

    // Puedes copiar también el método messages() de StoreHojaServicioRequest si quieres
    // public function messages(): array { ... }
}