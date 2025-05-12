<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HojaDeServicio;        // Modelo principal
use App\Models\RegistroIncidencia;    // Modelo para incidencias
use App\Models\ConfiguracionGlobal;   // Modelo para configuración global
use App\Http\Requests\StoreHojaServicioRequest; // Importar el Form Request para validación
use Illuminate\Http\Request;          // Necesario para index, update
use Illuminate\Support\Facades\DB;    // Para usar Transacciones
use Illuminate\Support\Facades\Log;   // Para usar Logs
use Illuminate\Http\JsonResponse;     // Para el tipo de retorno
use App\Http\Requests\UpdateHojaServicioRequest; // <-- Importar
use Barryvdh\DomPDF\Facade\Pdf; // Importa el Facade de DomPDF
use Illuminate\Http\Response; // Para el tipo de retorno del PDF
use Illuminate\Support\Facades\Storage; // Si guardas la imagen en storage
use Illuminate\Support\Facades\File; // Para leer la imagen
class HojaServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     * Muestra una lista paginada de Hojas de Servicio.
     */
    public function index(Request $request): JsonResponse // Añadir tipo de retorno
    {
        // --- Inicio Código Index (el que ya tenías y funciona) ---
        $query = HojaDeServicio::query();

        $query->with([
            // ¡OJO! Asegúrate que la tabla sea 'personal' o 'personals' aquí
            'personal:id,nombre,numero_tarjeta',
            'periodo:id,nombre'
        ]);

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->input('periodo_id'));
        }

        $query->orderBy('fecha_expedicion', 'desc');

        $hojas = $query->paginate(15);

        return response()->json($hojas);
         // --- Fin Código Index ---
    }

    /**
     * Store a newly created resource in storage.
     * Guarda una nueva Hoja de Servicio y sus incidencias.
     */
    public function store(StoreHojaServicioRequest $request): JsonResponse // Usar el Form Request para validación automática
    {
        // Log al inicio del método
        Log::info('--- [HojaServicioController@store] Iniciando ejecución ---');
        // validated() obtiene solo los datos que pasaron las reglas definidas en StoreHojaServicioRequest
        $validatedData = $request->validated();
        // Log::info('[HojaServicioController@store] Datos validados:', $validatedData); // Opcional

        // Obtener el jefe de RH predeterminado desde la configuración global
        $config = ConfiguracionGlobal::first(); // Asume que existe la fila con id=1 (creada por seeder)
        $jefeRhId = $config ? $config->jefe_rh_predeterminado_id : null;
        Log::info('[HojaServicioController@store] Jefe RH Predeterminado ID:', ['id' => $jefeRhId]);

        $hojaCreada = null; // Variable para guardar la hoja si se crea

        try {
            Log::info('[HojaServicioController@store] Iniciando DB::transaction...');
            // Usar una transacción para asegurar atomicidad
            DB::transaction(function () use ($validatedData, $jefeRhId, &$hojaCreada) {

                Log::info('[HojaServicioController@store] Dentro transacción: Creando HojaDeServicio...');
                // 1. Crear la Hoja de Servicio principal
                $nuevaHoja = HojaDeServicio::create([
                    'personal_id' => $validatedData['personal_id'],
                    'periodo_id' => $validatedData['periodo_id'],
                    'fecha_expedicion' => $validatedData['fecha_expedicion'],
                    'observaciones' => $validatedData['observaciones'] ?? null,
                    'jefe_inmediato_id' => $validatedData['jefe_inmediato_id'] ?? null,
                    'jefe_departamento_rh_id' => $jefeRhId, // Asignar el jefe de RH predeterminado
                ]);
                Log::info('[HojaServicioController@store] HojaDeServicio creada (dentro TX) con ID: ' . $nuevaHoja->id);

                // 2. Crear los registros de incidencia si existen en la petición
                if (!empty($validatedData['incidencias'])) {
                    Log::info('[HojaServicioController@store] Creando incidencias (dentro TX)...');
                    foreach ($validatedData['incidencias'] as $index => $incidenciaData) {
                        // Usamos la relación para crear la incidencia asociada
                        $inc = $nuevaHoja->registrosIncidencia()->create([
                            'tipo' => $incidenciaData['tipo'],
                            'fecha' => $incidenciaData['fecha'],
                            'descripcion' => $incidenciaData['descripcion'],
                        ]);
                         Log::info("[HojaServicioController@store] Incidencia #{$index} creada (dentro TX) con ID: " . $inc->id);
                    }
                    Log::info('[HojaServicioController@store] Incidencias creadas (dentro TX).');
                } else {
                     Log::info('[HojaServicioController@store] No se proporcionaron incidencias.');
                }

                // Guardamos la referencia para usarla después de la transacción
                $hojaCreada = $nuevaHoja;
                Log::info('[HojaServicioController@store] Fin bloque transacción. Hoja ID: ' . ($hojaCreada ? $hojaCreada->id : 'NULL'));

            }); // Fin DB::transaction

            // Si llegamos aquí, la transacción hizo COMMIT sin lanzar excepción
            Log::info('[HojaServicioController@store] Transacción completada exitosamente.');

            if ($hojaCreada) {
                 Log::info('[HojaServicioController@store] Preparando respuesta JSON 201 para Hoja ID: ' . $hojaCreada->id);
                 // Devolvemos la hoja creada con sus relaciones cargadas y status 201
                 return response()->json($hojaCreada->load(['personal', 'periodo', 'jefeInmediato', 'jefeDepartamentoRh', 'registrosIncidencia']), 201);
            } else {
                 // Esto sería MUY raro si la transacción no falló
                 Log::error('[HojaServicioController@store] ¡ERROR INESPERADO! La transacción terminó pero $hojaCreada es null.');
                 return response()->json(['message' => 'Error inesperado post-transacción.'], 500);
            }

        } catch (\Exception $e) {
            // Capturar CUALQUIER excepción durante el proceso
            Log::error('[HojaServicioController@store] EXCEPCIÓN CAPTURADA: ' . $e->getMessage(), [
                // 'exception_trace' => $e->getTraceAsString() // Descomentar para log de trace completo (muy largo)
                'exception_file' => $e->getFile(),
                'exception_line' => $e->getLine(),
            ]);
            // Devolver error genérico 500
            return response()->json(['message' => 'Error interno al guardar la hoja de servicio.'], 500);
        }
    }

  /**
 * Display the specified resource.
 * Devuelve los datos de una Hoja de Servicio específica, incluyendo sus relaciones
 * necesarias para el formulario de edición.
 *
 * @param  \App\Models\HojaDeServicio  $hoja La instancia inyectada automáticamente por Laravel (Route Model Binding)
 * @return \Illuminate\Http\JsonResponse
 */
public function show(HojaDeServicio $hoja): JsonResponse // Laravel busca la Hoja con el ID de la URL y la inyecta aquí como $hoja
{
    Log::info('[HojaServicioController@show] Solicitud para hoja ID: ' . $hoja->id);

    try {
        // Cargar todas las relaciones necesarias que el formulario de edición necesita
        // para mostrar los datos correctamente (selectores, incidencias, etc.)
        $hoja->load([
            'personal', // Carga el modelo Personal completo asociado
            'periodo',  // Carga el modelo PeriodoDisponible asociado
            'jefeInmediato', // Carga el modelo Personal del jefe inmediato (si existe)
            'jefeDepartamentoRh', // Carga el modelo Personal del jefe RH (si existe)
            'registrosIncidencia' // ¡IMPORTANTE! Carga la colección de todas las incidencias asociadas
        ]);

        // Devolver el modelo HojaDeServicio con todas las relaciones cargadas como JSON
        return response()->json($hoja);

    } catch (\Exception $e) {
        Log::error('Error al cargar Hoja de Servicio para mostrar: ID ' . $hoja->id . ' - ' . $e->getMessage());
        return response()->json(['message' => 'Error al obtener los detalles de la hoja de servicio.'], 500);
    }
}

    /**
     * Update the specified resource in storage.
     * (Implementar más adelante para editar)
     */
 // Dentro de HojaServicioController



// ... (otros imports y métodos index, store, show) ...

/**
 * Update the specified resource in storage.
 * Actualiza una Hoja de Servicio existente y sus incidencias.
 *
 * @param  \App\Http\Requests\UpdateHojaServicioRequest  $request
 * @param  \App\Models\HojaDeServicio  $hoja La hoja a actualizar (inyectada por Route Model Binding)
 * @return \Illuminate\Http\JsonResponse
 */
public function update(UpdateHojaServicioRequest $request, HojaDeServicio $hoja): JsonResponse
{
    Log::info('[HojaServicioController@update] Iniciando actualización para Hoja ID: ' . $hoja->id);
    $validatedData = $request->validated();
    // Log::info('[HojaServicioController@update] Datos validados:', $validatedData);

    try {
        DB::transaction(function () use ($hoja, $validatedData) {
            Log::info('[HojaServicioController@update] Dentro TX: Actualizando HojaDeServicio ID: ' . $hoja->id);

            // 1. Actualizar campos principales de la Hoja
            //    Asegúrate de que $validatedData solo contenga los campos que quieres actualizar.
            //    No actualizamos personal_id ni jefe_departamento_rh_id (este último viene de config).
            $hoja->update([
                'periodo_id' => $validatedData['periodo_id'],
                'fecha_expedicion' => $validatedData['fecha_expedicion'],
                'observaciones' => $validatedData['observaciones'] ?? $hoja->observaciones, // Mantener valor si no se envía? O poner null?
                'jefe_inmediato_id' => $validatedData['jefe_inmediato_id'] ?? null, // Permitir quitar jefe inmediato
            ]);
            Log::info('[HojaServicioController@update] HojaDeServicio actualizada.');

            // 2. Actualizar Incidencias (Estrategia: Borrar las viejas y Recrear las nuevas)
            //    Esta es la forma más simple de manejar cambios en un conjunto de detalles.
            Log::info('[HojaServicioController@update] Borrando incidencias antiguas para Hoja ID: ' . $hoja->id);
            $hoja->registrosIncidencia()->delete(); // ¡Borra todas las incidencias existentes para esta hoja!

            // 3. Crear las nuevas incidencias (si se enviaron en la petición)
            if (!empty($validatedData['incidencias'])) {
                Log::info('[HojaServicioController@update] Creando nuevas incidencias...');
                foreach ($validatedData['incidencias'] as $index => $incidenciaData) {
                    $inc = $hoja->registrosIncidencia()->create([
                        'tipo' => $incidenciaData['tipo'],
                        'fecha' => $incidenciaData['fecha'],
                        'descripcion' => $incidenciaData['descripcion'],
                    ]);
                    Log::info("[HojaServicioController@update] Nueva Incidencia #{$index} creada con ID: " . $inc->id);
                }
            } else {
                Log::info('[HojaServicioController@update] No se proporcionaron nuevas incidencias.');
            }
             Log::info('[HojaServicioController@update] Fin bloque transacción.');
        }); // Fin DB::transaction

        Log::info('[HojaServicioController@update] Transacción completada para Hoja ID: ' . $hoja->id);
        // Devolvemos la hoja actualizada con relaciones y status 200 OK
        return response()->json($hoja->fresh()->load(['personal', 'periodo', 'jefeInmediato', 'jefeDepartamentoRh', 'registrosIncidencia']), 200);

    } catch (\Exception $e) {
        Log::error('[HojaServicioController@update] EXCEPCIÓN CAPTURADA para Hoja ID ' . $hoja->id . ': ' . $e->getMessage(), [/*...*/]);
        return response()->json(['message' => 'Error interno al actualizar la hoja de servicio.'], 500);
    }
}
/**
 * Generate and return the PDF for the specified HojaDeServicio.
 *
 * @param  \App\Models\HojaDeServicio  $hoja
 * @return \Illuminate\Http\Response
 */
public function generatePdf(HojaDeServicio $hoja): Response
{
    Log::info('[HojaServicioController@generatePdf] Solicitud PDF para Hoja ID: ' . $hoja->id);

    try {
        // 1. Cargar todas las relaciones necesarias
        $hoja->load([
            'personal',
            'periodo',
            'jefeInmediato',
            'jefeDepartamentoRh', // El que está guardado en la hoja
            'registrosIncidencia'
        ]);

        // 2. Obtener configuración global (para membrete)
        $config = ConfiguracionGlobal::first(); // Asume que existe la fila id=1
        $rutaMembrete = $config ? $config->hoja_membretada_path : null;
        $membreteBase64 = null;

        // 3. Procesar imagen membretada si existe
        $membreteBase64 = null;
        $rutaMembrete = $config ? $config->hoja_membretada_path : null; // Obtener ruta de la BD

        // Verificar si la ruta existe en el disco 'public' de storage
        if ($rutaMembrete && Storage::disk('public')->exists($rutaMembrete)) {
            try {
                // Obtener tipo MIME usando Storage
                $tipoImagen = Storage::disk('public')->mimeType($rutaMembrete);
                // Obtener contenido binario del archivo usando Storage
                $imagenData = Storage::disk('public')->get($rutaMembrete);
                // Codificar a Base64
                $membreteBase64 = 'data:' . $tipoImagen . ';base64,' . base64_encode($imagenData);
                Log::info('[HojaServicioController@generatePdf] Membrete cargado desde storage: ' . $rutaMembrete);
            } catch (\Exception $imgEx) {
                Log::error('[HojaServicioController@generatePdf] Error al procesar imagen membrete desde storage: ' . $imgEx->getMessage());
                // Continuar sin membrete si falla la carga/procesamiento
            }
        } else if ($rutaMembrete) {
            Log::warning('[HojaServicioController@generatePdf] Ruta de membrete definida pero archivo no encontrado en storage/app/public/' . $rutaMembrete);
        }

        // 4. Preparar datos para la vista Blade (esta parte sigue igual)
        $data = [
        'hoja' => $hoja,
        'membreteBase64' => $membreteBase64, // Pasar la imagen Base64 (o null)
        ];

        Log::info('[HojaServicioController@generatePdf] Renderizando PDF...');
         
        // 5. Cargar la vista Blade, generar el PDF Y ESTABLECER TAMAÑO/ORIENTACIÓN
        $pdf = Pdf::loadView('pdfs.hoja_servicio', $data)
                ->setPaper('letter', 'portrait'); // <-- AÑADE ESTA LÍNEA AQUÍ

        // 6. Devolver el PDF para mostrar en navegador o descargar
        $nombreArchivo = 'hoja_servicio_' . $hoja->personal?->rfc . '_' . $hoja->id . '.pdf'; // Añadir ?-> por si personal es null

         // ->stream() muestra en navegador, ->download() fuerza descarga
         return $pdf->stream($nombreArchivo);

    } catch (\Exception $e) {
        Log::error('[HojaServicioController@generatePdf] EXCEPCIÓN al generar PDF para Hoja ID ' . $hoja->id . ': ' . $e->getMessage(), [
            'exception_trace' => $e->getTraceAsString()
        ]);
        // Devolver una respuesta de error HTML o JSON
        // Por ahora, un simple abort
         abort(500, 'Error al generar el PDF de la hoja de servicio.');
    }
}
// ... (método destroy) ...

    /**
     * Remove the specified resource from storage.
     * (Implementar más adelante si se necesita borrar)
     */
    public function destroy(HojaDeServicio $hojaDeServicio)
    {
         // Lógica para borrar la hoja (¡cuidado con las relaciones!)
         Log::warning('[HojaServicioController@destroy] Método no implementado.');
         return response()->json(['message' => 'Funcionalidad de borrar no implementada'], 501); // 501 Not Implemented
    }
}
