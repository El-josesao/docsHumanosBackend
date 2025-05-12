<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PeriodoDisponible; // Importar modelo
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; // Para logs

class PeriodoDisponibleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Devuelve la lista de periodos. Por defecto activos, o todos si se pide ?todos=1.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = PeriodoDisponible::query();

        // Devolver solo los activos por defecto, a menos que se pida ?todos=1
        if ($request->missing('todos') || $request->input('todos') != '1') {
             $query->where('activo', true);
        }

        // Ordenar por ID descendente (más nuevos primero) o por nombre ascendente
        $periodos = $query->orderBy('id', 'desc')->get(['id', 'nombre', 'activo']);

        return response()->json($periodos);
    }

    /**
     * Store a newly created resource in storage.
     * Crea un nuevo Periodo Disponible.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validar que el nombre sea requerido, string, único y no muy largo
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:periodos_disponibles,nombre'],
            'activo' => ['sometimes', 'boolean'], // 'activo' es opcional al crear
        ]);

        try {
            // Crear el nuevo periodo con los datos validados
            $periodo = PeriodoDisponible::create([
                'nombre' => $validated['nombre'],
                // Si no se envía 'activo', por defecto será true (definido en la migración)
                // Si se envía, se usa el valor validado (true/false)
                'activo' => $validated['activo'] ?? true,
            ]);

            Log::info('Nuevo Periodo Disponible creado:', ['id' => $periodo->id, 'nombre' => $periodo->nombre]);

            // Devolver el periodo recién creado con status 201 Created
            return response()->json($periodo, 201);

        } catch (\Exception $e) {
            Log::error('Error al crear Periodo Disponible: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al guardar el periodo.'], 500);
        }
    }

    // --- Aquí irían otros métodos si fueran necesarios ---
    // Por ejemplo, para activar/desactivar o eliminar periodos:
    // public function update(Request $request, PeriodoDisponible $periodo) { ... }
    // public function destroy(PeriodoDisponible $periodo) { ... }

}