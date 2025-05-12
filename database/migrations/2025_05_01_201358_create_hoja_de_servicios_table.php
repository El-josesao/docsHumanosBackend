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
        Schema::create('hojas_de_servicio', function (Blueprint $table) {
            // ---- Definir Columnas PRIMERO ----
            $table->id();
            // Columnas para las claves foráneas (UNSIGNED BIGINT para coincidir con id())
            $table->unsignedBigInteger('personal_id');
            $table->unsignedBigInteger('periodo_id');
            $table->unsignedBigInteger('jefe_inmediato_id')->nullable();
            $table->unsignedBigInteger('jefe_departamento_rh_id')->nullable();
            // Resto de columnas
            $table->date('fecha_expedicion');
            $table->text('observaciones')->nullable();
            $table->timestamps();
    
            // ---- Definir Claves Foráneas DESPUÉS ----
    
            // Constraint para personal_id
            $table->foreign('personal_id')
                  ->references('id')->on('personal') // <-- ¡OJO! Revisa si tu tabla se llama 'personals' (plural default) o 'personal'
                  ->restrictOnDelete();
    
            // Constraint para periodo_id
            $table->foreign('periodo_id')
                  ->references('id')->on('periodos_disponibles') // Esta tabla sí se llama así
                  ->restrictOnDelete();
    
            // Constraint para jefe_inmediato_id
            $table->foreign('jefe_inmediato_id')
                  ->references('id')->on('personal') // <-- ¡OJO! Revisa si tu tabla se llama 'personals' o 'personal'
                  ->nullOnDelete();
    
            // Constraint para jefe_departamento_rh_id
            $table->foreign('jefe_departamento_rh_id')
                  ->references('id')->on('personal') // <-- ¡OJO! Revisa si tu tabla se llama 'personals' o 'personal'
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_de_servicio');
    }
};
