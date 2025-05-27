<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaBuena extends Model
{
    use HasFactory;

    protected $table = 'notas_buenas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'personal_id',
        'jefe_otorga_id',
        'tipo_nota',
        'fecha_expedicion',
        'numero_oficio',
        'cuerpo',
        'referencia_articulos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_expedicion' => 'date',
    ];

    /**
     * Obtiene el registro de Personal que recibe la nota buena.
     */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    /**
     * Obtiene el registro de Personal (jefe) que otorga la nota buena.
     */
    public function jefeOtorga(): BelongsTo
    {
        // Usamos un nombre de relación diferente ('jefeOtorga') para evitar conflictos
        return $this->belongsTo(Personal::class, 'jefe_otorga_id');
    }
}