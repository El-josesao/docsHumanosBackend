<?php

namespace Database\Seeders;

// Asegúrate de importar el modelo y Seeder
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ConfiguracionGlobal; // <-- Importa tu modelo

class ConfiguracionGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Intenta encontrar la fila con id=1. Si no existe, la crea
        // con los valores especificados en el segundo array.
        // Si ya existe, no hace nada (o puedes añadir lógica para actualizarla si quisieras).
        ConfiguracionGlobal::firstOrCreate(
            ['id' => 1], // La condición para buscar/crear
            [
                // Valores iniciales si se crea la fila:
                'jefe_rh_predeterminado_id' => null, // Empezamos sin jefe de RH asignado
                'hoja_membretada_path' => null,      // Empezamos sin ruta de membrete
            ]
        );

        // Mensaje opcional para la consola
        $this->command->info('Seeder de ConfiguracionGlobal ejecutado: Fila única asegurada/creada con id=1.');
    }
}