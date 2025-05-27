<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Importar Controladores ---
// Importa el controlador de autenticación que creamos
use App\Http\Controllers\Api\AuthController;
// Importa aquí otros controladores de API que vayas creando
use App\Http\Controllers\Api\HojaServicioController;
use App\Http\Controllers\Api\PersonalController;

use App\Http\Controllers\Api\PeriodoDisponibleController;
// use App\Http\Controllers\Api\ConfiguracionController;
// use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\ConfiguracionController;

// --- Rutas Públicas / Autenticación ---

// Endpoint para iniciar sesión (POST)
// Debe ser accesible sin estar autenticado.
Route::post('/login', [AuthController::class, 'login'])->name('api.login');


// --- Rutas Protegidas (Requieren Autenticación vía Sanctum) ---
// El middleware 'auth:sanctum' asegura que solo usuarios autenticados
// (con una sesión/cookie válida) puedan acceder a estas rutas.
Route::middleware('auth:sanctum')->group(function () {

    // Endpoint para obtener los datos del usuario actualmente autenticado (GET)
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');

    // Endpoint para cerrar la sesión del usuario actual (POST)
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    // Dentro de Route::middleware('auth:sanctum')->group(...)

    // Ruta para obtener la lista de Hojas de Servicio (GET)
    Route::get('/hojas', [HojaServicioController::class, 'index'])->name('api.hojas.index');
    Route::post('/hojas', [HojaServicioController::class, 'store'])->name('api.hojas.store');
    Route::put('/hojas/{hoja}', [HojaServicioController::class, 'update'])->name('api.hojas.update');
    Route::get('/hojas/{hoja}', [HojaServicioController::class, 'show'])->name('api.hojas.show');
    // Ruta para generar el PDF de una hoja específica
    Route::get('/hojas/{hoja}/pdf', [HojaServicioController::class, 'generatePdf'])->name('api.hojas.pdf');
    Route::post('/configuracion/upload-membrete', [ConfiguracionController::class, 'uploadHojaMembretada'])->name('api.configuracion.upload');

    Route::get('/personal', [PersonalController::class, 'index'])->name('api.personal.index');
    Route::post('/personal/import', [PersonalController::class, 'import'])->name('api.personal.import');



    // Ejemplo para Personal (para llenar selectores en el frontend)
    // Route::get('/personal', [PersonalController::class, 'index'])->name('api.personal.index');
    // --- Rutas de Configuración ---
    Route::get('/configuracion', [ConfiguracionController::class, 'show'])->name('api.configuracion.show');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('api.configuracion.update'); // Usamos PUT para actualizar el recurso completo (o los campos que permitamos)
    // La ruta para subir membrete la añadiremos cuando implementemos esa función específica

    // Ejemplo para Periodos Disponibles
    // Route::get('/periodos', [PeriodoDisponibleController::class, 'index'])->name('api.periodos.index');
        // Ruta GET para LISTAR periodos (ya debería funcionar)
    Route::get('/periodos', [PeriodoDisponibleController::class, 'index'])->name('api.periodos.index');

    // Ruta POST para CREAR un periodo - ¡ESTA ES LA CLAVE!
    Route::post('/periodos', [PeriodoDisponibleController::class, 'store'])->name('api.periodos.store');

    // --- Rutas para Notas Buenas ---
    Route::get('/notas-buenas/{notaBuena}/pdf', [App\Http\Controllers\Api\NotaBuenaController::class, 'generatePdf'])->name('api.notas-buenas.pdf');
    Route::apiResource('notas-buenas', App\Http\Controllers\Api\NotaBuenaController::class);

}); // Fin del grupo middleware auth:sanctum


// Ruta "fallback" opcional para cualquier otra ruta API no definida
Route::fallback(function(){
    return response()->json(['message' => 'Ruta API no encontrada.'], 404);
});