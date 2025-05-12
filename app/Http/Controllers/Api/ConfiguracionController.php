<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionGlobal; // Modelo para la tabla configuracion_global
use App\Models\Personal;           // Modelo Personal para validación
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;   // Para logs
use Illuminate\Validation\Rule;      // Para reglas de validación avanzadas
use Illuminate\Support\Facades\Storage; // <-- Para manejar archivos
use Illuminate\Validation\Rules\File; // <-- Para reglas de validación de archivos (Laravel 9+)

class ConfiguracionController extends Controller
{
    /**
     * Display the global configuration settings.
     * Muestra la configuración global (asume una única fila con ID=1).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(): JsonResponse
{
    Log::info('[ConfiguracionController@show] Solicitud para obtener configuración global.');
    $config = ConfiguracionGlobal::firstOrCreate(
        ['id' => 1],
        ['jefe_rh_predeterminado_id' => null, 'hoja_membretada_path' => null]
    )->load('jefeRhPredeterminado');

    // --- AÑADIR ESTA LÓGICA ---
    // Generar URL absoluta para la imagen si existe la ruta y el archivo
    if ($config->hoja_membretada_path && Storage::disk('public')->exists($config->hoja_membretada_path)) {
        // Storage::url() genera la URL completa basada en tu APP_URL y la config de filesystems
        $config->hoja_membretada_url = Storage::disk('public')->url($config->hoja_membretada_path);
        Log::info('[ConfiguracionController@show] URL de membrete generada: ' . $config->hoja_membretada_url);
    } else {
        $config->hoja_membretada_url = null; // Añadir la propiedad como nula si no hay imagen
    }
    // --- FIN LÓGICA AÑADIDA ---

    return response()->json($config);
}

    /**
     * Store a newly created resource in storage.
     * (No aplicable para configuración global, ya que solo actualizamos la fila existente)
     */
    public function store(Request $request)
    {
        // Normalmente no crearíamos nuevas filas de configuración global.
        // Podríamos redirigir a update o devolver un error.
        return response()->json(['message' => 'Método no permitido. Use PUT para actualizar la configuración existente.'], 405); // 405 Method Not Allowed
    }

    /**
     * Display the specified resource.
     * (Redundante con show() ya que solo hay una fila)
     */
    public function display(ConfiguracionGlobal $configuracionGlobal) // El nombre del parámetro debe coincidir con el de la ruta si usas Route Model Binding
    {
         // Por simplicidad, redirigimos a show() o devolvemos lo mismo.
         return $this->show();
    }


    /**
     * Update the specified resource in storage.
     * Actualiza la configuración global (por ahora, solo el jefe de RH).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id (Aunque siempre será 1, podemos recibirlo o ignorarlo)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse // No usamos Route Model Binding aquí, siempre actualizamos ID=1
    {
        Log::info('[ConfiguracionController@update] Iniciando actualización de configuración global.');

        // 1. Buscar la fila de configuración (ID=1)
        $config = ConfiguracionGlobal::find(1);
        if (!$config) {
             // Si no existe (muy raro después de show/seeder), la creamos
             $config = ConfiguracionGlobal::create([
                 'id' => 1,
                 'jefe_rh_predeterminado_id' => null,
                 'hoja_membretada_path' => null
             ]);
             Log::warning('[ConfiguracionController@update] Fila de configuración ID=1 no existía, se ha creado.');
        }

        // 2. Validar los datos entrantes
        //    Permitimos que el ID sea nulo (para quitar jefe) o un ID válido en la tabla 'personal'
        //    ¡¡OJO!! Asegúrate que el nombre de la tabla sea 'personal' o 'personals'.
        $validated = $request->validate([
            'jefe_rh_predeterminado_id' => ['nullable', 'integer', Rule::exists('personal', 'id')],
            // Añadir aquí validación para 'hoja_membretada_path' cuando implementemos la subida
            // 'hoja_membretada_path' => ['nullable', 'string', 'max:2048'],
        ]);
        // Log::info('[ConfiguracionController@update] Datos validados:', $validated);


        // 3. Actualizar la configuración
        //    Usamos update() sobre la instancia encontrada/creada.
        //    Solo actualizamos los campos que validamos.
        $updateData = [];
        if (array_key_exists('jefe_rh_predeterminado_id', $validated)) {
            $updateData['jefe_rh_predeterminado_id'] = $validated['jefe_rh_predeterminado_id'];
        }
        // Añadir aquí la actualización de 'hoja_membretada_path' cuando se implemente

        if (!empty($updateData)) {
            $config->update($updateData);
            Log::info('[ConfiguracionController@update] Configuración global actualizada.', $updateData);
        } else {
             Log::info('[ConfiguracionController@update] No se recibieron datos válidos para actualizar.');
        }

        // 4. Devolver la configuración actualizada con la relación cargada
        return response()->json($config->fresh()->load('jefeRhPredeterminado')); // fresh() re-obtiene el modelo de la BD
    }

    /**
     * Remove the specified resource from storage.
     * (No aplicable, no queremos borrar la fila de configuración)
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Operación no permitida.'], 405);
    }

    // --- Métodos Adicionales (Ejemplo: para subir membrete) ---
    
    /**
 * Handle the upload of the letterhead image.
 * Guarda la nueva imagen y actualiza la ruta en la configuración.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\JsonResponse
 */
public function uploadHojaMembretada(Request $request): JsonResponse
{
    Log::info('[ConfiguracionController@uploadHojaMembretada] Iniciando subida de membrete.');

    // 1. Validar el archivo subido
    //    'membrete' es el nombre del campo que esperamos del FormData del frontend.
    $validated = $request->validate([
        'membrete' => [
            'required',
            // Validar que sea una imagen con tipos MIME y tamaño específicos
            File::image()
                ->max(2 * 1024) // Tamaño máximo en kilobytes (ej. 2MB)
                ->dimensions(Rule::dimensions()->maxWidth(2000)->maxHeight(2000)), // Dimensiones máximas opcionales
             // O forma más simple (menos específica con tipos):
             // 'image',
             // 'mimes:jpg,jpeg,png,gif',
             // 'max:2048', // max 2MB
        ],
    ]);
    Log::info('[ConfiguracionController@uploadHojaMembretada] Archivo validado.');


    // 2. Buscar la fila de configuración (o crearla si no existe)
    $config = ConfiguracionGlobal::firstOrCreate(['id' => 1]);

    // 3. Borrar la imagen anterior del storage si existe
    if ($config->hoja_membretada_path) {
        // Asumimos que la ruta guardada es relativa al disco 'public'
        if (Storage::disk('public')->exists($config->hoja_membretada_path)) {
            Storage::disk('public')->delete($config->hoja_membretada_path);
            Log::info('[ConfiguracionController@uploadHojaMembretada] Membrete anterior eliminado: ' . $config->hoja_membretada_path);
        } else {
             Log::warning('[ConfiguracionController@uploadHojaMembretada] Ruta de membrete anterior no encontrada en disco public: ' . $config->hoja_membretada_path);
        }
    }

    // 4. Guardar la nueva imagen en el storage público
    //    Guardará dentro de 'storage/app/public/membretes' con un nombre único.
    //    Devuelve la ruta relativa: 'membretes/nombre_unico.jpg'
    try {
        $path = $request->file('membrete')->store('membretes', 'public');
        Log::info('[ConfiguracionController@uploadHojaMembretada] Nuevo membrete guardado en: ' . $path);
    } catch (\Exception $e) {
         Log::error('[ConfiguracionController@uploadHojaMembretada] Error al guardar archivo: ' . $e->getMessage());
          return response()->json(['message' => 'Error al guardar el archivo en el servidor.'], 500);
    }


    // 5. Actualizar la ruta en la base de datos
    $config->update(['hoja_membretada_path' => $path]);
    Log::info('[ConfiguracionController@uploadHojaMembretada] Ruta actualizada en BD.');


    // 6. Devolver la configuración actualizada (incluyendo la nueva ruta)
    return response()->json($config->fresh()->load('jefeRhPredeterminado'));
}
    
}