<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Nota Buena - {{ $notaBuena->personal?->rfc ?? 'N/A' }}</title>
    <style>
        @page {
            /* Puedes definir márgenes de página si no usas membrete completo */
            /* margin: 50px; */
            margin: 0; /* Mantenemos en 0 si el membrete es una imagen de fondo completa */
        }

        body {
            /* Fuentes: Noto Sans, luego Montserrat, luego Helvetica/Arial como fallback */
            font-family: 'Noto Sans', 'Montserrat', 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt; /* Un poco más pequeño para que quepa más */
            line-height: 1.3; /* Reducido para juntar más las líneas */
            /* Padding para crear márgenes internos donde irá el contenido */
            padding: 60px 60px 40px 60px; /* Arriba, Derecha, Abajo, Izquierda - Ajusta según necesites */

            @if($membreteBase64)
                background-image: url("{{ $membreteBase64 }}");
                background-repeat: no-repeat;
                background-position: center top;
                background-size: contain; /* Prueba 'cover' si quieres que llene más, o '100% auto' para ancho completo */
            @endif
        }

        .contenido-principal {
             /* Ajusta este margen si el membrete se superpone. Si no hay membrete, puede ser 0 o un valor pequeño. */
            margin-top: {{ $membreteBase64 ? '110px' : '9px' }}; /* Reducido */
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .text-left { text-align: left; } /* Para asegurar alineación a la izquierda */

        /* Márgenes y paddings más controlados */
        .mt-1 { margin-top: 0.15rem; }
        .mt-2 { margin-top: 0.3rem; }
        .mt-4 { margin-top: 0.6rem; }
        .mt-6 { margin-top: 1rem; }
        .mt-8 { margin-top: 1.5rem; } /* Reducido */

        .mb-1 { margin-bottom: 0.15rem; }
        .mb-2 { margin-bottom: 0.3rem; }
        .mb-4 { margin-bottom: 0.6rem; }

        p {
            margin-top: 0;
            margin-bottom: 0.3rem; /* Espacio pequeño por defecto entre párrafos */
            text-align: left; /* Alineación a la izquierda por defecto para párrafos */
        }

        .oficio-info {
            margin-bottom: 15px; /* Reducido */
        }
        .oficio-info p {
            margin-bottom: 2px; /* Muy poco espacio entre líneas de oficio y fecha */
            text-align: right; /* Mantenemos esto a la derecha */
        }

        .destinatario {
            margin-bottom: 15px; /* Reducido */
        }
        .destinatario p {
            margin-bottom: 2px; /* Muy poco espacio entre líneas del destinatario */
        }

        .referencia-articulos {
            text-align: justify; /* Justificado puede verse bien para bloques de texto legales */
            font-size: 11pt; /* Un poco más pequeño para destacar menos */
            color: #222; /* Ligeramente más oscuro */
            margin-top: 10px; /* Espacio antes */
            margin-bottom: 0px; /* Reducido 
            white-space: pre-wrap;*/
            line-height: 1.2; /* Más apretado para este bloque */
        }
        .cuerpo-nota {
            text-align: justify; /* Justificado para el cuerpo principal */
            margin-top: 0px;  /* Espacio antes */
            margin-bottom: 15px; /* Reducido */
            white-space: pre-wrap;
        }

        .firma-seccion {
            margin-top: 30px; /* Reducido significativamente */
        }
        .firma-seccion p {
            text-align: center; /* Centramos todo en la sección de firma */
            margin-bottom: 2px;
        }
        .lema {
            font-style: italic;
            font-size: 9pt;
            margin-top: 0.15rem;
            margin-bottom: 10px; /* Espacio después del lema y antes de la línea */
        }
        .firma-linea {
            border-top: 1px solid black;
            width: 50%; /* Más corta para un look más limpio */
            margin: 15px auto 5px auto; /* Reducido el margen superior */
        }
        .firma-nombre {
            font-weight: bold;
        }
        .firma-puesto {
            font-size: 9pt;
        }

        .institucion { /* Aplicado a "INSTITUTO TECNOLÓGICO..." y "Puesto" */
            font-size: 10pt; /* Reducido */
            /* text-transform: uppercase; */ /* Quitado para un look menos formal si prefieres */
        }
        .ccp {
            margin-top: 20px; /* Reducido */
            font-size: 8pt; /* Más pequeño */
        }
        .ccp p {
            text-align: left; /* Alineado a la izquierda */
        }

    </style>
</head>
<body>
    <div class="contenido-principal">
        <div class="oficio-info text-right">
            <p class="text-bold">OFICIO NÚMERO: {{ $notaBuena->numero_oficio ?? 'S/N' }}</p>
            <p>JIQUILPAN, MICH., A {{ $notaBuena->fecha_expedicion ? $notaBuena->fecha_expedicion->translatedFormat('d \d\e F \d\e Y') : 'Fecha no especificada' }}.</p>
        </div>

        <div class="destinatario mt-8">
            <p class="text-bold">{{ $notaBuena->personal?->nombre ?? 'Nombre del Empleado no disponible' }}</p>
            {{-- Puedes añadir más detalles del empleado si los necesitas y los tienes en el modelo Personal --}}
            <p class="text-bold institucion">{{ $notaBuena->personal?->puesto ?? 'Puesto no disponible' }}</p>
            <p class="text-bold institucion">INSTITUTO TECNOLÓGICO DE JIQUILPAN</p>
            <p class="text-bold">P R E S E N T E.</p>
        </div>

        @if($notaBuena->referencia_articulos)
            <div class="referencia-articulos">
                <p>{{ $notaBuena->referencia_articulos }}</p>
            </div>
        @endif

        <div class="cuerpo-nota">
            <p>{{ $notaBuena->cuerpo }}</p>
        </div>

        <div class="firma-seccion text-center mt-8">
            <p class="text-bold">ATENTAMENTE</p>
            <p class="lema mt-1 mb-4">"Justicia Social en la Tecnificación Industrial"</p>
            <div><p><br><br></p></div>
            <div class="firma-linea"></div>
            <p class="firma-nombre">{{ $notaBuena->jefeOtorga?->nombre ?? 'Nombre del Jefe no disponible' }}</p>
            <p class="firma-puesto">{{ $notaBuena->jefeOtorga?->puesto ?? 'Puesto del Jefe no disponible' }}</p>
        </div>

        <div class="ccp">
            <p>c.c.p. Expediente Depto. de Recursos Humanos.</p>
        </div>
    </div>
</body>
</html>