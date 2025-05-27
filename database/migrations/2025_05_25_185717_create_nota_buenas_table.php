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
        Schema::create('notas_buenas', function (Blueprint $table) {
            $table->id();

            // Clave foránea para el personal que recibe la nota.
            // Se enlaza con la tabla 'personal'. Si se intenta borrar un personal que tiene notas, la operación fallará.
            $table->foreignId('personal_id')->constrained('personal')->restrictOnDelete();

            // Clave foránea para el jefe que emite la nota.
            // También se enlaza con la tabla 'personal'.
            $table->foreignId('jefe_otorga_id')->constrained('personal')->restrictOnDelete();

            // Para diferenciar entre los tipos de nota, por ejemplo: 'asistencia', 'general', 'desempeno_destacado', etc.
            $table->string('tipo_nota');

            // La fecha que aparece en el documento expedido.
            $table->date('fecha_expedicion');

            // El número de oficio de referencia, por ejemplo: "RHI:023/2024". Es opcional.
            $table->string('numero_oficio')->nullable();

            // Un campo de texto grande para el contenido principal del documento.
            // Para la nota de asistencia, aquí se guardará el texto estándar sobre no tener faltas/retardos.
            // Para la nota general, aquí se guardará el texto que el usuario redacte libremente.
            $table->text('cuerpo');

            // Texto que hace referencia a los artículos del reglamento.
            // Es opcional, principalmente para la nota de asistencia.
            $table->text('referencia_articulos')->nullable();

            // Los campos `created_at` y `updated_at` para el registro de fechas.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_buenas');
    }
};