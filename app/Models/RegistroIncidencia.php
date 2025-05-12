<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroIncidencia extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'registros_incidencia';

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'hoja_servicio_id',
        'tipo',
        'fecha',
        'descripcion',
    ];

    /**
     * Indicates if the model should be timestamped.
     * Comenta o elimina esta línea si SÍ quieres usar timestamps (created_at, updated_at)
     * Déjala como está si NO quieres timestamps.
     * @var bool
     */
    // public $timestamps = false; // Decide si usas timestamps o no (ver migración)

     /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'fecha' => 'date',
    ];


    // --- RELACIONES ---

    /**
     * Obtiene la HojaDeServicio a la que pertenece este registro de incidencia.
     */
    public function hojaDeServicio(): BelongsTo
    {
        // Un RegistroIncidencia pertenece a una HojaDeServicio
        return $this->belongsTo(HojaDeServicio::class, 'hoja_servicio_id');
    }
}