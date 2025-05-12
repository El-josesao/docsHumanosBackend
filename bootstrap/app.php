<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Configuración principal de la aplicación Laravel
return Application::configure(basePath: dirname(__DIR__))

    // --- Configuración de Rutas ---
    ->withRouting(
        // Carga las rutas web estándar
        web: __DIR__.'/../routes/web.php',

        // --- ¡¡ESTA LÍNEA ES LA QUE HAY QUE AÑADIR!! ---
        // Carga las rutas API desde routes/api.php
        // Laravel aplicará automáticamente el prefijo /api y el middleware 'api'
        api: __DIR__.'/../routes/api.php',

        // Carga las rutas para los comandos de consola
        commands: __DIR__.'/../routes/console.php',

        // Define la ruta para las verificaciones de salud (health checks)
        health: '/up',
    )

    // --- Configuración de Middleware ---
    ->withMiddleware(function (Middleware $middleware) {
        // Aquí registramos el middleware de Sanctum para manejar
        // la autenticación SPA basada en cookies/sesión
        $middleware->statefulApi();

        // Aquí se pueden añadir otros middleware globales o de grupo si es necesario
        // Ejemplo: $middleware->alias([...]);
        // Ejemplo: $middleware->append(...);
    })

    // --- Configuración de Excepciones ---
    ->withExceptions(function (Exceptions $exceptions) {
        // Aquí se personaliza el manejo de errores/excepciones globales
        // Por ejemplo:
        // $exceptions->report(function (Throwable $e) {
        //     // ... lógica para reportar errores
        // });
        // $exceptions->render(function (NotFoundHttpException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json(['message' => 'Recurso no encontrado.'], 404);
        //     }
        // });
    })

    // --- Creación de la Instancia de la Aplicación ---
    ->create();