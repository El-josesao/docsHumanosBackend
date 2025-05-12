<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void  // <-- Corregido: añadido tipo de retorno void
     */
    public function up(): void // <-- Corregido: añadido tipo de retorno void
    {
        Schema::create('configuracion_global', function (Blueprint $table) {
            // Columna ID estándar, aunque solo habrá una fila (id=1)
            $table->id();

            // Clave foránea para el jefe de RH predeterminado (opcional)
            // Referencia a la tabla 'personal', columna 'id'
            // Si se borra el registro del jefe en 'personal', esta columna se pondrá a NULL
            $table->foreignId('jefe_rh_predeterminado_id')
                  ->nullable()               // Permite que sea nulo
                  ->constrained('personal')  // Constraint a la tabla 'personal'
                  ->nullOnDelete();         // ON DELETE SET NULL

            // Columna para almacenar la ruta/URL de la imagen membretada
            $table->string('hoja_membretada_path')->nullable();

            // Columnas estándar 'created_at' y 'updated_at'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void  // <-- Corregido: añadido tipo de retorno void
     */
    public function down(): void // <-- Corregido: añadido tipo de retorno void
    {
        Schema::dropIfExists('configuracion_global');
    }
};