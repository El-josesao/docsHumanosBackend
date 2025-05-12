<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 
// Necesitaremos HasMany para la relación con incidencias más adelante
// use Illuminate\Database\Eloquent\Relations\HasMany;

class HojaDeServicio extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Especificar explícitamente es buena práctica.
     * @var string
     */
    protected $table = 'hojas_de_servicio';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'personal_id',
        'periodo_id',
        'fecha_expedicion',
        'observaciones',
        'jefe_inmediato_id',
        'jefe_departamento_rh_id',
    ];

    /**
     * The attributes that should be cast.
     * Para asegurar que la fecha se trate como objeto Carbon/Date.
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_expedicion' => 'date',
    ];

    // --- RELACIONES ---

    /**
     * Obtiene el registro de Personal al que pertenece esta hoja.
     */
    public function personal(): BelongsTo
    {
        // Una HojaDeServicio pertenece a un Personal
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    /**
     * Obtiene el PeriodoDisponible al que pertenece esta hoja.
     */
    public function periodo(): BelongsTo
    {
        // Una HojaDeServicio pertenece a un PeriodoDisponible
        return $this->belongsTo(PeriodoDisponible::class, 'periodo_id');
    }

    /**
     * Obtiene el registro de Personal que es el Jefe Inmediato (si existe).
     */
    public function jefeInmediato(): BelongsTo
    {
        // Una HojaDeServicio pertenece a un Personal (como jefe inmediato)
        // El segundo argumento es la foreign key en esta tabla (hojas_de_servicio)
        // El tercer argumento (owner key) se infiere como 'id' en la tabla 'personal'
        return $this->belongsTo(Personal::class, 'jefe_inmediato_id');
    }

    /**
     * Obtiene el registro de Personal que es el Jefe de RH (si existe).
     */
    public function jefeDepartamentoRh(): BelongsTo
    {
        // Una HojaDeServicio pertenece a un Personal (como jefe RH)
        return $this->belongsTo(Personal::class, 'jefe_departamento_rh_id');
    }

    /**
     * Obtiene los registros de incidencia asociados a esta hoja.
     * (La definiremos completa cuando creemos el modelo RegistroIncidencia)
     */
    public function registrosIncidencia(): HasMany
    {
        // Una HojaDeServicio tiene muchos RegistroIncidencia
        return $this->hasMany(RegistroIncidencia::class, 'hoja_servicio_id');
    }
}