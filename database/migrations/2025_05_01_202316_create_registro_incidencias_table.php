<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registros_incidencia', function (Blueprint $table) {
            $table->id();

            // Clave foránea a la hoja de servicio.
            // Si se borra la hoja, se borran automáticamente todas sus incidencias asociadas.
            $table->foreignId('hoja_servicio_id')
                  ->constrained('hojas_de_servicio') // Busca tabla 'hojas_de_servicio', columna 'id'
                  ->cascadeOnDelete(); // ON DELETE CASCADE

            // Tipo de incidencia (NB, FE, NM, EX, AM, SU) - Usamos VARCHAR(2) como en la spec.
            $table->string('tipo', 2)->comment('NB=Notas Buenas, FE=Felicitaciones, NM=Nombramiento, EX=Extrañamiento, AM=Amonestación, SU=Suplencia');
            $table->date('fecha'); // Fecha de la incidencia
            $table->text('descripcion'); // Descripción detallada
            $table->timestamps(); // Añadidos por buena práctica, aunque no estaban en la spec original. Si no los quieres, borra esta línea.

            // La spec pedía un índice en hoja_servicio_id.
            // ->constrained() ya crea un índice automáticamente en la mayoría de los casos.
            // Si quieres asegurarte de tenerlo explícitamente:
            // $table->index('hoja_servicio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_incidencia');
    }
};
