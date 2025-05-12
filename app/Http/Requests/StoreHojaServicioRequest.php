<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Para authorize

class StoreHojaServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Asumimos que cualquier usuario logueado puede crear.
     */
    public function authorize(): bool
    {
        // Asegúrate que solo usuarios autenticados puedan usar esta validación/request
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Definir las reglas de validación para cada campo esperado del frontend
        return [
            // Campos principales
            // ¡¡OJO con el nombre de la tabla 'personal' vs 'personals'!! Usa el correcto.
            'personal_id' => ['required', 'integer', 'exists:personal,id'],
            'periodo_id' => ['required', 'integer', 'exists:periodos_disponibles,id'],
            'fecha_expedicion' => ['required', 'date_format:Y-m-d'],
            'observaciones' => ['nullable', 'string', 'max:5000'], // Añadir límite de texto
            // ¡¡OJO con el nombre de la tabla 'personal' vs 'personals'!! Usa el correcto.
            'jefe_inmediato_id' => ['nullable', 'integer', 'different:personal_id', 'exists:personal,id'], // Jefe no puede ser el mismo empleado

            // Reglas para el array de incidencias
            'incidencias' => ['nullable', 'array', 'max:20'], // Puede ser nulo o array, con máximo 20 incidencias?
            // Reglas para cada elemento (*) dentro del array 'incidencias'
            // 'required_with:incidencias' significa que estos campos son requeridos SOLO SI el array 'incidencias' no está vacío/nulo
            'incidencias.*.tipo' => ['required_with:incidencias', 'string', 'size:2', 'in:NB,FE,NM,EX,AM,SU'], // Validar tipos permitidos
            'incidencias.*.fecha' => ['required_with:incidencias', 'date_format:Y-m-d'],
            'incidencias.*.descripcion' => ['required_with:incidencias', 'string', 'min:1', 'max:1000'], // Límite para descripción
        ];
    }

     /**
     * Get custom messages for validator errors.
     * (Opcional: personalizar mensajes para que sean más amigables)
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'personal_id.required' => 'Debe seleccionar un empleado.',
            'personal_id.exists' => 'El empleado seleccionado no es válido.',
            'periodo_id.required' => 'Debe seleccionar un periodo.',
            'periodo_id.exists' => 'El periodo seleccionado no es válido.',
            'fecha_expedicion.required' => 'La fecha de expedición es obligatoria.',
            'fecha_expedicion.date_format' => 'El formato de fecha debe ser AAAA-MM-DD.',
            'jefe_inmediato_id.exists' => 'El jefe inmediato seleccionado no es válido.',
            'jefe_inmediato_id.different' => 'El jefe inmediato no puede ser el mismo empleado.',
            'incidencias.max' => 'No puedes añadir más de 20 incidencias.',
            'incidencias.*.tipo.required_with' => 'El tipo es obligatorio para cada incidencia.',
            'incidencias.*.tipo.size' => 'El tipo debe ser de 2 caracteres.',
             'incidencias.*.tipo.in' => 'El tipo de incidencia seleccionado no es válido.',
            'incidencias.*.fecha.required_with' => 'La fecha es obligatoria para cada incidencia.',
            'incidencias.*.fecha.date_format' => 'El formato de fecha de incidencia debe ser AAAA-MM-DD.',
            'incidencias.*.descripcion.required_with' => 'La descripción es obligatoria para cada incidencia.',
            'incidencias.*.descripcion.min' => 'La descripción de la incidencia no puede estar vacía.',
             'incidencias.*.descripcion.max' => 'La descripción de la incidencia es muy larga.',
        ];
    }
}