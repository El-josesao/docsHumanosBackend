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
    Schema::create('personal', function (Blueprint $table) {
        $table->id(); // PK estándar

        // --- Datos de Identificación ---
        $table->string('numero_tarjeta')->unique()->comment('Número de tarjeta único'); // TU ID sugerido, como columna UNIQUE
        $table->string('nombre'); // Nombre completo, iniciar con apellido paterno
        $table->string('rfc')->unique(); // RFC único (ya lo teníamos)
        $table->string('curp')->unique()->nullable(); // CURP único, podría ser nulo si no aplica a todos?
        $table->string('titulo')->nullable()->comment('Ej: Lic., Ing., Mtro., Dr.'); // Abreviatura grado académico
        $table->date('fecha_nacimiento')->nullable();
        $table->string('sexo', 10)->nullable(); // 'Masculino', 'Femenino', u otros? O char(1)?

        // --- Datos Laborales / Fechas Clave ---
        $table->date('fecha_ingreso_gob_fed')->nullable()->comment('Fecha Ingreso Gobierno Federal (G.F.)');
        $table->date('fecha_ingreso_sep')->nullable()->comment('Fecha Ingreso SEP (S.E.P.)');
        $table->date('fecha_ingreso_rama')->nullable()->comment('Fecha Ingreso Dependencia (RAMA)');
        $table->string('funcion')->nullable();
        $table->string('area')->nullable(); // Área de adscripción
        $table->string('puesto')->nullable(); // Puesto funcional (ya lo teníamos)
        $table->string('clave_academica')->nullable()->comment('Columna ACAD?'); // ¿Qué representa ACAD? Usé clave_academica como ejemplo

        // --- Datos Académicos / Estatus ---
        $table->string('nivel_estudio')->nullable();
        $table->string('profesion')->nullable();
        $table->string('sni', 10)->nullable()->comment('Sistema Nacional de Investigadores (No, C, I, II, III, E)'); // SNI (string para niveles)
        $table->boolean('docente')->nullable()->default(false); // ¿Es docente?
        $table->boolean('interno')->nullable()->default(false); // ¿Es interno?
        $table->integer('plazas_activas')->unsigned()->nullable()->comment('Número de Plazas Activas (PA)'); // PA (Numérico)
        $table->string('motivo_ausencia')->nullable()->comment('Mot/Ausencia'); // Motivo ausencia/baja temporal?
        $table->string('estatus', 50)->default('Activo'); // Estatus (Activo, Baja, Licencia, etc.)

        $table->timestamps(); // created_at, updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personals');
    }
};
