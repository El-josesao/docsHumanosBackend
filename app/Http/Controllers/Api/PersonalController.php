<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Personal; // <-- Importar el modelo
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Para el tipo de retorno
use Illuminate\Support\Facades\Log; // Opcional, para logs
use Maatwebsite\Excel\Facades\Excel;       // Facade de Maatwebsite
use App\Imports\PersonalImport;             // Tu clase de importación
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException; // Excepción específica de Maatwebsite
class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     * Devuelve una lista de todo el personal, optimizada para selectores.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        // Seleccionamos solo las columnas necesarias para los selectores
        // y quizás alguna extra como numero_tarjeta si la mostramos.
        // Ordenamos por nombre para que el selector aparezca ordenado.
        $personal = Personal::select(['id', 'nombre', 'rfc', 'numero_tarjeta', 'puesto']) // Ajusta campos si necesitas más/menos
                          ->orderBy('nombre', 'asc')
                          ->get(); // Obtenemos todos los registros

        // Devolvemos la colección como JSON
        return response()->json($personal);
    }

    /**
     * Maneja la subida del archivo para importación masiva de Personal.
     * Por ahora solo valida la presencia y tipo de archivo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    
     /**
 /**
     * Maneja la subida e importación de un archivo (CSV/Excel) para Personal.
     * Utiliza PersonalImport para procesar y validar las filas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        Log::info('[PersonalController@import] Solicitud de importación recibida.');

        // 1. Validar que se recibió un archivo válido
        try {
            $validated = $request->validate([
                // 'archivo_personal' debe coincidir con el 'name' del input file en el frontend
                'archivo_personal' => [
                    'required',
                    'file',
                    'mimes:csv,txt,xlsx,xls', // Tipos permitidos
                    'max:5120', // Límite 5MB (ajustable en KB)
                ]
            ]);
            $file = $validated['archivo_personal'];
            Log::info('[PersonalController@import] Archivo validado inicialmente.', [
                'name' => $file->getClientOriginalName(),
                'size' => round($file->getSize() / 1024) . ' KB',
            ]);

        } catch (LaravelValidationException $e) {
            Log::error('[PersonalController@import] Falló validación inicial del archivo.', $e->errors());
            // Devolver error 422 si falla la validación del archivo en sí
            return response()->json([
                'message' => 'Error en el archivo subido.',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Intentar importar usando la clase PersonalImport
        try {
            Log::info('[PersonalController@import] Iniciando Excel::import...');

            // Maatwebsite se encargará de leer el archivo $file,
            // aplicar las reglas de rules() de PersonalImport a cada fila,
            // y usar model() + uniqueBy() para crear/actualizar en la BD.
            Excel::import(new PersonalImport, $file);

            Log::info('[PersonalController@import] Importación completada sin excepciones de validación.');
            // Podríamos intentar obtener contadores si la clase Import los proveyera,
            // pero por ahora devolvemos un mensaje genérico de éxito.
            return response()->json(['message' => 'Archivo procesado. La importación se completó (o actualizó registros existentes).'], 200);

        } catch (ExcelValidationException $e) {
            // Capturar errores de validación POR FILA detectados por Maatwebsite
            $failures = $e->failures(); // Array de objetos Failure
            $errorMessages = [];
            $limitedFailures = 0;
            foreach ($failures as $failure) {
                 $errorMessages[] = "Fila: " . $failure->row() . ". Columna: '" . $failure->attribute() . "'. Error(es): " . implode(', ', $failure->errors());
                 $limitedFailures++;
                 if ($limitedFailures >= 20) { // Limitar número de errores devueltos
                     $errorMessages[] = "...y posiblemente más errores.";
                     break;
                 }
            }
            Log::warning('[PersonalController@import] Errores de validación durante la importación.', ['failure_count' => count($failures)]);
            // Devolvemos un error 422 con detalles de las filas que fallaron
            return response()->json([
                'message' => 'El archivo contiene errores de validación. Algunas filas no se importaron.',
                'validation_errors' => $errorMessages // Devolvemos los mensajes de error formateados
            ], 422);

        } catch (\Exception $e) {
            // Capturar cualquier otro error inesperado durante la importación
            Log::error('[PersonalController@import] Error inesperado procesando archivo: ' . $e->getMessage(), [
                 'exception_trace' => $e->getTraceAsString() // Útil para depurar
            ]);
            return response()->json(['message' => 'Ocurrió un error inesperado durante el procesamiento del archivo.'], 500);
        }
    }

    // ... (resto de métodos del controlador: show, update, destroy si los tienes) ...

    // Aquí irían otros métodos si necesitas CRUD completo para Personal vía API
    // public function store(Request $request) { ... }
    // public function show(Personal $personal) { ... } // $personal ya estaría cargado por Route Model Binding
    // public function update(Request $request, Personal $personal) { ... }
    // public function destroy(Personal $personal) { ... }
}