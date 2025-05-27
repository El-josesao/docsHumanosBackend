<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotaBuena;
use App\Models\ConfiguracionGlobal; // Para el membrete
use App\Http\Requests\StoreNotaBuenaRequest; // Importar el Request para validación de store
// Considera crear un UpdateNotaBuenaRequest si las reglas de actualización son diferentes
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response; // Para el tipo de retorno del PDF
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Para el membrete
use Barryvdh\DomPDF\Facade\Pdf; // Importa el Facade de DomPDF

class NotaBuenaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        Log::info('[NotaBuenaController@index] Solicitud para listar Notas Buenas.');
        $notas = NotaBuena::with(['personal:id,nombre,numero_tarjeta', 'jefeOtorga:id,nombre'])
            ->orderBy('fecha_expedicion', 'desc')
            ->paginate(15);

        return response()->json($notas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotaBuenaRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        Log::info('[NotaBuenaController@store] Iniciando creación de Nota Buena.', $validatedData);

        try {
            $notaBuena = NotaBuena::create($validatedData);
            Log::info('[NotaBuenaController@store] Nota Buena creada con ID: ' . $notaBuena->id);

            // Devolvemos la nota creada con sus relaciones cargadas
            return response()->json(
                $notaBuena->load(['personal', 'jefeOtorga']),
                201
            );
        } catch (\Exception $e) {
            Log::error('[NotaBuenaController@store] EXCEPCIÓN: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al guardar la nota buena.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(NotaBuena $notaBuena): JsonResponse
    {
        Log::info('[NotaBuenaController@show] Solicitud para nota buena ID: ' . $notaBuena->id);
        // Aseguramos cargar las relaciones necesarias para la vista de detalle o edición.
        return response()->json($notaBuena->load(['personal', 'jefeOtorga']));
    }

    /**
     * Generate and return the PDF for the specified NotaBuena.
     */
    public function generatePdf(NotaBuena $notaBuena): Response
    {
        Log::info('[NotaBuenaController@generatePdf] Solicitud PDF para Nota Buena ID: ' . $notaBuena->id);

        try {
            // 1. Cargar las relaciones necesarias para el PDF
            //    Si 'personal' y 'jefeOtorga' ya están cargados (ej. por eager loading en la ruta o implicit binding),
            //    esto no hará consultas adicionales. Si no, las cargará.
            $notaBuena->loadMissing(['personal', 'jefeOtorga']);

            // 2. Obtener configuración global (para membrete)
            $config = ConfiguracionGlobal::first(); // Asume que existe la fila id=1
            $membreteBase64 = null;
            $rutaMembrete = $config ? $config->hoja_membretada_path : null; //

            if ($rutaMembrete && Storage::disk('public')->exists($rutaMembrete)) { //
                try {
                    $tipoImagen = Storage::disk('public')->mimeType($rutaMembrete); //
                    $imagenData = Storage::disk('public')->get($rutaMembrete); //
                    $membreteBase64 = 'data:' . $tipoImagen . ';base64,' . base64_encode($imagenData); //
                    Log::info('[NotaBuenaController@generatePdf] Membrete cargado desde storage: ' . $rutaMembrete);
                } catch (\Exception $imgEx) {
                    Log::error('[NotaBuenaController@generatePdf] Error al procesar imagen membrete desde storage: ' . $imgEx->getMessage());
                }
            } elseif ($rutaMembrete) {
                Log::warning('[NotaBuenaController@generatePdf] Ruta de membrete definida pero archivo no encontrado: ' . $rutaMembrete);
            }

            // 3. Preparar datos para la vista Blade
            $data = [
                'notaBuena' => $notaBuena,
                'membreteBase64' => $membreteBase64,
            ];

            Log::info('[NotaBuenaController@generatePdf] Renderizando PDF para Nota Buena ID: ' . $notaBuena->id);
            
            // 4. Cargar la vista Blade, generar el PDF y establecer tamaño/orientación
            $pdf = Pdf::loadView('pdfs.nota_buena', $data)
                    ->setPaper('letter', 'portrait'); // Tamaño carta, orientación vertical

            // 5. Devolver el PDF
            $nombreArchivo = 'nota_buena_' . ($notaBuena->personal?->rfc ?? $notaBuena->id) . '.pdf'; //
            
            // ->stream() muestra en navegador, ->download() fuerza descarga
            return $pdf->stream($nombreArchivo); //

        } catch (\Exception $e) {
            Log::error('[NotaBuenaController@generatePdf] EXCEPCIÓN al generar PDF para Nota Buena ID ' . $notaBuena->id . ': ' . $e->getMessage(), [
                'exception_trace' => $e->getTraceAsString() // Esto puede ser muy largo para logs de producción
            ]);
            abort(500, 'Error al generar el PDF de la nota buena.'); //
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotaBuena $notaBuena)
    {
        // TODO: Implementar validación usando un FormRequest (ej. UpdateNotaBuenaRequest)
        // $validatedData = $request->validate([...]);
        Log::info('[NotaBuenaController@update] Solicitud para actualizar Nota Buena ID: ' . $notaBuena->id, $request->all());
        
        // try {
        //     $notaBuena->update($validatedData); // Asegúrate que $validatedData contenga solo los campos permitidos
        //     Log::info('[NotaBuenaController@update] Nota Buena actualizada con ID: ' . $notaBuena->id);
        //     return response()->json($notaBuena->load(['personal', 'jefeOtorga']));
        // } catch (\Exception $e) {
        //     Log::error('[NotaBuenaController@update] EXCEPCIÓN: ' . $e->getMessage());
        //     return response()->json(['message' => 'Error interno al actualizar la nota buena.'], 500);
        // }
        return response()->json(['message' => 'Funcionalidad de actualizar no implementada aún'], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotaBuena $notaBuena)
    {
        Log::info('[NotaBuenaController@destroy] Solicitud para eliminar Nota Buena ID: ' . $notaBuena->id);
        // try {
        //     $notaBuena->delete();
        //     Log::info('[NotaBuenaController@destroy] Nota Buena eliminada con ID: ' . $notaBuena->id);
        //     return response()->json(['message' => 'Nota Buena eliminada correctamente.'], 200);
        // } catch (\Exception $e) {
        //     Log::error('[NotaBuenaController@destroy] EXCEPCIÓN: ' . $e->getMessage());
        //     return response()->json(['message' => 'Error interno al eliminar la nota buena.'], 500);
        // }
        return response()->json(['message' => 'Funcionalidad de eliminar no implementada aún'], 501);
    }
}