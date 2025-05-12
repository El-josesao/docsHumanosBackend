<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hoja de Servicio - {{ $hoja->personal?->rfc ?? 'N/A' }}</title>
    <style>
        /* --- Estilos CSS para el PDF --- */

        /* Configuración de Página: Sin márgenes */
        @page {
            margin: 0;
        }

        /* Estilos del Cuerpo Principal */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif; /* Fuentes comunes compatibles */
            font-size: 10pt;
            /* Padding para crear márgenes internos donde irá el contenido */
            /* Ajusta estos valores según el diseño de tu membrete */
            padding: 60px 50px 50px 50px; /* Arriba, Derecha, Abajo, Izquierda */
            line-height: 1.2; /* Interlineado */

            /* Aplicar imagen membrete como fondo del body */
            @if($membreteBase64) /* Solo si existe la imagen Base64 */
                background-image: url("{{ $membreteBase64 }}");
                background-repeat: no-repeat;
                background-position: center top; /* Centrado arriba */
                /* Ajusta cómo se escala la imagen: */
                background-size: cover; /* Cubre todo manteniendo proporción (puede recortar) */
                /* O prueba: background-size: contain; */ /* Muestra toda la imagen (puede dejar bandas) */
                /* O prueba: background-size: 100% 100%; */ /* Estira para cubrir exacto (puede distorsionar) */
            @endif
        }
        .contenido{
            margin-top:8em;
        }
        /* Estilos Generales para Contenido */
        h1, h2, h3 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 10px;
            font-weight: bold;
        }
        h1 { font-size: 12pt; margin-bottom: 2px; }
        h2 { font-size: 12pt; margin-bottom: 2px; }
        h3 { font-size: 10pt; font-weight: normal; margin-bottom: 10px; } /* Título periodo */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        th, td {
            border: 1px solid #999; /* Borde más suave */
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #e9e9e9; /* Fondo de cabecera más suave */
            font-weight: bold;
        }
        /* Evitar saltos de página dentro de filas */
        tr { page-break-inside: avoid; }

        /* Estilos específicos */
        .seccion {
            margin-bottom: 15px;
            page-break-inside: avoid; /* Intentar mantener secciones juntas */
        }
        .seccion-titulo {
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            background-color: #f0f0f0;
            padding: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px; /* Esquinas redondeadas */
        }
        .campo-label {
            font-weight: bold;
        }
        .campo-valor {
             /* Puedes añadir estilos aquí si necesitas */
        }
        .incidencia-item {
            border-bottom: 1px dotted #ccc;
            padding-bottom: 8px;
            margin-bottom: 8px;
            page-break-inside: avoid;
        }
        .incidencia-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .incidencia-descripcion {
            margin-left: 10px;
            /* white-space: pre-wrap; */ /* Permite saltos de línea y espacios */
            /* DomPDF a veces tiene problemas con pre-wrap, prueba sin él si da problemas */
            padding-top: 3px;
        }
        .firmas {
            margin-top: 60px; /* Más espacio antes de las firmas */
            width: 100%;
        }
        .firma-linea {
            border-top: 1px solid black;
            width: 70%; /* Línea un poco más larga */
            margin: 40px auto 5px auto;
            text-align: center;
            font-size: 9pt;
        }
        .firma-nombre {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            line-height: 1.2;
        }
         .firma-puesto {
            text-align: center;
            font-size: 9pt;
            line-height: 1.2;
        }
         /* Utilidad para texto sin salto */
        .no-wrap { white-space: nowrap; }

    </style>
</head>
<body>

    <div class="contenido">

        <h1>INSTITUTO TECNOLÓGICO NACIONAL DE MÉXICO</h1> <h2>INSTITUTO TECNOLÓGICO DE JIQUILPAN</h2> <h2>HOJA DE SERVICIO</h2>
        <h3>PERIODO: {{ $hoja->periodo?->nombre ?? 'N/A' }}</h3>

        <div class="seccion">
            <div class="seccion-titulo">DATOS DEL TRABAJADOR</div>
            <table>
                <tr>
                    <td class="no-wrap"><span class="campo-label">No. Tarjeta:</span></td>
                    <td>{{ $hoja->personal?->numero_tarjeta ?? 'N/A' }}</td>
                    <td width="25%" class="no-wrap"><span class="campo-label">Nombre:</span></td>
                    <td width="75%">{{ $hoja->personal?->nombre ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="no-wrap"><span class="campo-label">RFC:</span></td>
                    <td>{{ $hoja->personal?->rfc ?? 'N/A' }}</td>
                    <td class="no-wrap"><span class="campo-label">CURP:</span></td>
                    <td>{{ $hoja->personal?->curp ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="no-wrap"><span class="campo-label">Puesto:</span></td>
                    <td colspan="3">{{ $hoja->personal?->puesto ?? 'N/A' }}</td>
                    </tr>
            </table>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">REGISTRO DE INCIDENCIAS</div>
            @if($hoja->registrosIncidencia->isEmpty())
                <p style="text-align: center; color: #777;">-- Sin incidencias registradas para este periodo --</p>
            @else
                {{-- Mapeo de códigos a descripciones (puedes mover esto a un Helper o al Modelo si prefieres) --}}
                @php
                    $tiposDesc = [
                        'NB' => 'Notas Buenas', 'FE' => 'Felicitaciones', 'NM' => 'Nombramiento',
                        'EX' => 'Extrañamiento', 'AM' => 'Amonestación', 'SU' => 'Suplencia'
                    ];
                @endphp
                @foreach($hoja->registrosIncidencia->sortBy('fecha') as $incidencia)
                    <div class="incidencia-item">
                        <span class="campo-label">Tipo:</span> {{ $tiposDesc[$incidencia->tipo] ?? $incidencia->tipo }}
                        <span style="margin-left: 20px;">
                            <span class="campo-label">Fecha:</span>
                            {{-- Formatear fecha usando Carbon (Laravel lo hace si la columna está en $casts) --}}
                            {{ $incidencia->fecha ? $incidencia->fecha->format('d/m/Y') : 'N/A' }}
                        </span>
                        <div style="margin-top: 5px;">
                            <span class="campo-label">Descripción:</span>
                            <div class="incidencia-descripcion">{{ $incidencia->descripcion }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

         @if($hoja->observaciones)
            <div class="seccion">
                <div class="seccion-titulo">OBSERVACIONES</div>
                <div style="white-space: pre-wrap;">{{ $hoja->observaciones }}</div>
            </div>
         @endif

        <div style="margin-top: 30px; text-align: right; font-size: 10pt;">
            Jiquilpan, Michoacán a {{ $hoja->fecha_expedicion ? $hoja->fecha_expedicion->translatedFormat('d \d\e F \d\e Y') : 'Fecha no especificada' }}
        </div>

        <table class="firmas">
            <tr>
                <td style="width: 50%; border: none; padding: 5px;">
                     <div class="firma-linea"></div>
                     <div class="firma-nombre">{{ $hoja->jefeInmediato?->nombre ?? '(Sin Jefe Inmediato Asignado)' }}</div>
                     <div class="firma-puesto">{{ $hoja->jefeInmediato?->puesto ?? 'JEFE INMEDIATO' }}</div>
                </td>
                <td style="width: 50%; border: none; padding: 5px;">
                     <div class="firma-linea"></div>
                     <div class="firma-nombre">{{ $hoja->jefeDepartamentoRh?->nombre ?? '(Jefe RH No Asignado)' }}</div>
                     <div class="firma-puesto">{{ $hoja->jefeDepartamentoRh?->puesto ?? 'DEPTO. DE RECURSOS HUMANOS' }}</div>
                </td>
            </tr>
        </table>

    </div> </body>
</html>