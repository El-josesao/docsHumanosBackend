<?php

return [
    // Asegúrate que tus rutas API, login, logout y sanctum/csrf-cookie estén cubiertas
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'], // Permitir métodos comunes

    // ¡CLAVE! Define tu origen frontend específico usando una variable de entorno
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],

    // ¡CLAVE! Sé específico con las cabeceras permitidas, incluye X-XSRF-TOKEN
     'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Accept',
        'Origin',
        'Authorization', // Si usaras tokens API en el futuro
        'X-XSRF-TOKEN', // Necesario para protección CSRF de Sanctum
    ],

    'exposed_headers' => [],

    'max_age' => 0,

    // ¡¡CLAVE!! Asegúrate de que sea true para permitir cookies de sesión
    'supports_credentials' => true,
];