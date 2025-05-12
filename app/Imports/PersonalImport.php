<?php

namespace App\Imports;

use App\Models\Personal; // Modelo Eloquent
use Maatwebsite\Excel\Concerns\ToModel; // Para mapear fila a modelo
use Maatwebsite\Excel\Concerns\WithHeadingRow; // Para usar nombres de cabecera
use Maatwebsite\Excel\Concerns\WithValidation; // Para validar filas
use Maatwebsite\Excel\Concerns\WithUpserts; // Para insertar o actualizar (evitar duplicados)
use Maatwebsite\Excel\Concerns\WithBatchInserts; // Optimización: insertar en lotes
use Maatwebsite\Excel\Concerns\WithChunkReading; // Optimización: leer en bloques
use Illuminate\Validation\Rule; // Para reglas de validación avanzadas

class PersonalImport implements ToModel, WithHeadingRow, WithValidation, WithUpserts, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row Un array asociativo donde las claves son los nombres de las cabeceras del archivo CSV/Excel (en minúsculas y snake_case usualmente)
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // --- ¡¡IMPORTANTE!! ---
        // Aquí mapeas las columnas de tu archivo a los campos de tu modelo.
        // ASEGÚRATE que las claves del array $row (ej. 'numero_tarjeta', 'nombre', 'rfc', etc.)
        // coincidan EXACTAMENTE con los nombres de las cabeceras de tu archivo Excel/CSV
        // (Maatwebsite las convierte a snake_case y minúsculas por defecto).
        // Puedes usar Log::info($row); aquí para ver qué claves te llegan la primera vez.

        // Ejemplo de mapeo (¡¡AJUSTA ESTOS NOMBRES DE CLAVE SEGÚN TU ARCHIVO!!):
        return new Personal([
            'numero_tarjeta'  => $row['numero_tarjeta'] ?? $row['numero'] ?? null, // Busca 'numero_tarjeta' o 'numero'
            'nombre'          => $row['nombre'] ?? $row['nombre'] ?? null,
            'rfc'             => $row['rfc'] ?? null,
            'curp'            => $row['curp'] ?? null,
            'titulo'          => $row['titulo'] ?? null,
            'fecha_nacimiento'=> $this->transformDate($row['fecha_de_nacimiento'] ?? null), // Necesitamos transformar fechas
            'sexo'            => $row['sexo'] ?? null,
            'fecha_ingreso_gob_fed' => $this->transformDate($row['gf'] ?? null),
            'fecha_ingreso_sep' => $this->transformDate($row['sep'] ?? null),
            'fecha_ingreso_rama' => $this->transformDate($row['rama'] ?? null),
            'funcion'         => $row['funcion'] ?? null,
            'area'            => $row['area'] ?? null,
            'puesto'          => $row['puesto'] ?? null,
            'clave_academica' => $row['acad'] ?? null, // Asumiendo ACAD -> clave_academica
            'nivel_estudio'   => $row['nivel_de_estudio'] ?? null,
            'profesion'       => $row['profesion'] ?? null,
            'sni'             => $row['sni'] ?? null,
            'docente'         => $this->transformBoolean($row['docente'] ?? null), // Transformar 'Si'/'No' a true/false
            'interno'         => $this->transformBoolean($row['interno'] ?? null),
            'plazas_activas'  => $row['pa'] ?? null,
            'motivo_ausencia' => $row['mot_ausencia'] ?? null,
            'estatus'         => $row['estatus'] ?? 'Activo', // Default a 'Activo' si no viene
        ]);
    }

    /**
     * Define las reglas de validación para cada fila del archivo.
     * Las claves deben coincidir con las cabeceras del archivo (snake_case, minúsculas).
     */
    public function rules(): array
    {
        // --- ¡¡IMPORTANTE!! ---
        // Ajusta estas reglas según las columnas EXACTAS y requerimientos de tu archivo.
        return [
            // Clave => Reglas (ver https://laravel.com/docs/validation#available-validation-rules)
            //'numero_tarjeta' => ['required', 'string', 'max:50', Rule::unique('personal', 'numero_tarjeta')], // Único en la tabla 'personal' (¡OJO! singular aquí)
            'numero_tarjeta' => ['required', 'max:50', Rule::unique('personal', 'numero_tarjeta')],
            'nombre' => ['required', 'string', 'max:255'],
            'rfc' => ['required', 'string', 'size:13', Rule::unique('personal', 'rfc')], // Único en tabla 'personal' (¡OJO!)
            'curp' => ['nullable', 'string', 'size:18', Rule::unique('personal', 'curp')->whereNotNull('curp')], // Único si no es nulo
            'fecha_de_nacimiento' => ['nullable', 'date_format:d/m/Y'], // ¡AJUSTA EL FORMATO DE FECHA!
            'gf' => ['nullable', 'date_format:d/m/Y'], // ¡AJUSTA EL FORMATO DE FECHA!
            'sep' => ['nullable', 'date_format:d/m/Y'], // ¡AJUSTA EL FORMATO DE FECHA!
            'rama' => ['nullable', 'date_format:d/m/Y'], // ¡AJUSTA EL FORMATO DE FECHA!
            'puesto' => ['nullable', 'string', 'max:255'],
            'docente' => ['nullable', 'string', Rule::in(['Docente', 'No Docente'])], // Validar valores booleanos comunes
            'interno' => ['nullable', 'string', Rule::in(['Si', 'No', 'si', 'no', 'SI', 'NO', 1, 0])],
            'estatus' => ['nullable', 'string', Rule::in(['Activo', 'Inactivo', 'Baja', 'Licencia'])], // Validar estatus permitidos

            // Valida las otras columnas que tengas en tu archivo...
             '*.titulo' => ['nullable', 'string', 'max:50'],
             '*.sexo' => ['nullable', 'string', 'max:20'],
             '*.funcion' => ['nullable', 'string', 'max:255'],
             '*.area' => ['nullable', 'string', 'max:255'],
             '*.acad' => ['nullable', 'string', 'max:50'],
             '*.nivel_de_estudio' => ['nullable', 'string', 'max:100'],
             '*.profesion' => ['nullable', 'string', 'max:255'],
             '*.sni' => ['nullable', 'string', 'max:10'],
             '*.pa' => ['nullable', 'integer'],
             '*.mot_ausencia' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Define la(s) columna(s) que identifican unívocamente a un registro
     * para saber si debe actualizarse (upsert) en lugar de insertarse.
     * Debe coincidir con una cabecera del archivo.
     */
    public function uniqueBy()
    {
        // --- ¡¡DECISIÓN IMPORTANTE!! ---
        // Elige el campo que MEJOR identifique a un empleado existente.
        // ¿RFC? ¿Número de Tarjeta? ¿CURP? Debe ser único en tu archivo y BD.
         return 'rfc'; // Ejemplo: si el RFC es el identificador principal
        // return 'numero_tarjeta'; // O si prefieres usar el número de tarjeta
    }

    /**
     * Define cuántos modelos se insertarán en la base de datos a la vez.
     */
    public function batchSize(): int
    {
        return 100; // Inserta de 100 en 100 (ajustable)
    }

    /**
     * Define cuántas filas leerá del archivo a la vez para procesar en memoria.
     */
    public function chunkSize(): int
    {
        return 100; // Procesa de 100 en 100 filas (ajustable)
    }

    /**
     * Helper para transformar fechas del formato del archivo al formato de BD (YYYY-MM-DD)
     * ¡AJUSTA 'd/m/Y' AL FORMATO REAL DE TU ARCHIVO!
     */
    private function transformDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            // Intenta parsear la fecha asumiendo formato DD/MM/YYYY
            return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            // Si falla, intenta otros formatos comunes o devuelve null/error
             try { return \Carbon\Carbon::parse($value)->format('Y-m-d'); } catch (\Exception $e2) { return null; }
        }
    }

     /**
     * Helper para transformar 'Si'/'No' a booleano (true/false)
     */
    private function transformBoolean($value): bool
    {
        if (empty($value)) {
            return false;
        }
        $normalized = strtolower(trim((string)$value));
        return in_array($normalized, ['si', 'yes', 'true', '1']);
    }
}