<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Facade de Autenticación
use Illuminate\Validation\ValidationException; // Para errores de validación
// Importa tu modelo User si no es el default App\Models\User
// use App\Models\User;

class AuthController extends Controller
{
    /**
     * Maneja el intento de inicio de sesión.
     */
    public function login(Request $request)
    {
        // 1. Validar los datos del formulario (email y password requeridos)
        $credentials = $request->validate([
            // Asegúrate que el frontend envíe 'email' o ajusta aquí si envía 'username'
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Intentar autenticar usando el guard 'web' (predeterminado para Sanctum SPA)
        // El segundo argumento 'true' opcional es para "Recuérdame" (si implementas el checkbox)
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            // 3. Si la autenticación es exitosa, regenerar la sesión
            $request->session()->regenerate();

            // Opcional: Log para debugging
            \Log::info('Login successful for user: ' . Auth::user()->email);

            // 4. Devolver respuesta exitosa (puede ser solo un 204 No Content o datos del usuario)
            // Devolver el usuario es útil para que el frontend lo guarde en Pinia
            return response()->json(Auth::user());
            // o return response()->noContent();
        }

        // 5. Si la autenticación falla
        \Log::warning('Login failed for email: ' . $request->email);
        // Lanzar una excepción de validación que Laravel convierte en respuesta JSON 422
        throw ValidationException::withMessages([
            // Usa la clave del campo que falló (puede ser 'email' o una genérica)
            'email' => [__('auth.failed')], // Busca el mensaje en lang/es/auth.php
        ]);
    }

    /**
     * Obtiene el usuario autenticado actualmente.
     */
    public function user(Request $request)
    {
        // Gracias al middleware 'auth:sanctum', si llegamos aquí, el usuario está autenticado.
        // Simplemente devolvemos el usuario asociado a la sesión/cookie actual.
        return response()->json($request->user());
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        // Cerrar la sesión usando el guard 'web'
        Auth::guard('web')->logout();

        // Invalidar la sesión actual para limpiarla completamente
        $request->session()->invalidate();

        // Regenerar el token CSRF para la siguiente sesión
        $request->session()->regenerateToken();

        // Devolver respuesta exitosa
        return response()->json(['message' => 'Logout successful']);
    }
}
