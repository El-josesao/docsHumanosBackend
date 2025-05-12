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
        Schema::create('periodos_disponibles', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('nombre')->unique(); // Nombre del periodo (ej: "Enero-Junio 2025"), debe ser único
            $table->boolean('activo')->default(true); // Para poder "desactivar" periodos viejos sin borrarlos
            $table->timestamps(); // created_at, updated_at (buena práctica)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_disponibles');
    }
};
