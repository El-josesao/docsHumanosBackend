<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeriodoDisponible; // <-- 1. Importar el modelo

class PeriodoDisponibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 2. Definir los periodos iniciales
        $periodos = [
            // [ 'nombre' => 'Nombre del Periodo', 'activo' => true/false ],
            [ 'nombre' => 'Enero - Junio 2024', 'activo' => true ],
            [ 'nombre' => 'Julio - Diciembre 2024', 'activo' => true ],
            [ 'nombre' => 'Anual 2024', 'activo' => true ],
            [ 'nombre' => 'Enero - Junio 2025', 'activo' => true ], // Periodo actual/futuro
            [ 'nombre' => 'Anual 2025', 'activo' => true ],
            [ 'nombre' => 'Anual 2023', 'activo' => false ], // Ejemplo de periodo inactivo
            [ 'nombre' => 'Julio - Diciembre 2023', 'activo' => false ],
            [ 'nombre' => 'Enero - Junio 2023', 'activo' => false ],

        ];

        $this->command->info('Creando/actualizando Periodos Disponibles de ejemplo...');

        // 3. Iterar y crear/actualizar cada periodo
        foreach ($periodos as $periodoData) {
            // updateOrCreate busca un registro que coincida con el primer array (el campo único 'nombre')
            // Si lo encuentra, actualiza sus datos con los del segundo array.
            // Si no lo encuentra, crea un nuevo registro uniendo los datos de ambos arrays.
            // Esto evita que se creen periodos duplicados si ejecutas 'db:seed' varias veces.
            PeriodoDisponible::updateOrCreate(
                ['nombre' => $periodoData['nombre']], // Criterio para buscar (debe ser único)
                ['activo' => $periodoData['activo']]   // Datos a establecer o actualizar
            );
        }

        $this->command->info('¡Periodos Disponibles creados/actualizados!');
    }
}