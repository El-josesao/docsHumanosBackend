<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    use HasFactory;
 /**
     * The table associated with the model.
     * Especificamos explícitamente para evitar problemas.
     * Asegúrate que este nombre coincida EXACTAMENTE con tu tabla en la BD.
     * @var string
     */
    protected $table = 'personal'; // <-- AÑADE O VERIFICA ESTA LÍNEA (con 's')

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_tarjeta',
        'nombre',
        'rfc',
        'curp',
        'titulo',
        'fecha_nacimiento',
        'sexo',
        'fecha_ingreso_gob_fed',
        'fecha_ingreso_sep',
        'fecha_ingreso_rama',
        'funcion',
        'area',
        'puesto',
        'clave_academica',
        'nivel_estudio',
        'profesion',
        'sni',
        'docente',
        'interno',
        'plazas_activas',
        'motivo_ausencia',
        'estatus',
    ];
}
