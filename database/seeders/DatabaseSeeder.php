<?php

namespace Database\Seeders;

// Importa la clase base Seeder
use Illuminate\Database\Seeder;
// No necesitamos importar User aquí si no usamos el factory directamente

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Este método se ejecuta cuando corres 'php artisan db:seed' sin especificar una clase.
     *
     * @return void
     */
    public function run(): void
    {
        // --- Comentamos o eliminamos la creación de usuarios con Factory ---
        // Si quieres mantener la creación de usuarios de prueba por defecto, descomenta
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // -------------------------------------------------------------------

        // --- Llamar a nuestros Seeders personalizados ---
        // Usamos el método call() para ejecutar otros seeders en el orden que queramos.
        // Es buena idea ejecutar primero el de configuración global.
        $this->call([
            ConfiguracionGlobalSeeder::class,   // Ejecuta el seeder para la tabla de configuración
            PeriodoDisponibleSeeder::class,     // Ejecuta el seeder para los periodos
            // Puedes añadir más seeders aquí a medida que los crees
            // Ejemplo: PersonalSeeder::class, HojaDeServicioSeeder::class, etc.
        ]);

        // Puedes añadir un mensaje final si lo deseas
        $this->command->info('Todos los seeders registrados en DatabaseSeeder se han ejecutado.');
    }
}