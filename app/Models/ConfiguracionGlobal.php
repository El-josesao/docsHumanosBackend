<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Necesario para la relación

class ConfiguracionGlobal extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Especificar explícitamente es buena práctica.
     * @var string
     */
    protected $table = 'configuracion_global';

    /**
     * The attributes that are mass assignable.
     * Define qué campos se pueden llenar usando create() o update() masivamente.
     * @var array<int, string>
     */
    protected $fillable = [
        'jefe_rh_predeterminado_id',
        'hoja_membretada_path',
    ];

    // --- RELACIONES ---

    /**
     * Obtiene el registro de Personal que es el Jefe de RH predeterminado (si existe).
     */
    public function jefeRhPredeterminado(): BelongsTo
    {
        // La configuración "pertenece a" un Personal (como jefe RH predeterminado)
        // El segundo argumento es la clave foránea en ESTA tabla ('configuracion_global')
        return $this->belongsTo(Personal::class, 'jefe_rh_predeterminado_id');
    }

    // No se necesitan casts especiales aquí a menos que añadas más campos después.
}