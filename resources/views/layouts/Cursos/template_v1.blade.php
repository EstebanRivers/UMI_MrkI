<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Finalización</title>
    <style>
        /* Configuración general de la página y tipografía */
        @page {
            margin: 0cm 0cm; /* Sin márgenes para que el fondo cubra todo */
        }
        body {
            font-family: 'serif'; /* Usa una fuente serif elegante */
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            /* Fondo dinámico del curso */
            /* Usamos public_path para que DomPDF lo lea directamente del disco */
            background-image: url('{{ public_path("storage/" . $course->cert_bg_path) }}');
            background-position: center center;
            background-repeat: no-repeat;
            /* Ajustar el tamaño del fondo para que cubra un A4 landscape */
            background-size: 29.7cm 21cm; 
        }

        /* Contenedor principal con padding para no pegarse al borde del marco */
        .container {
            padding: 60px; /* Ajusta esto según el grosor del marco de tu imagen de fondo */
            text-align: center;
            color: #333; /* Color de texto oscuro, ajusta según tu fondo */
        }

        /* Logo de la institución arriba */
        .top-logo {
            height: 80px; /* Tamaño fijo para el logo */
            margin-bottom: 30px;
        }
        .top-logo img {
            max-height: 100%;
            width: auto;
        }
        
        /* Títulos y Textos */
        h1.main-title {
            font-size: 36pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            color: #cfa158; /* Color dorado/ocre similar al ejemplo */
        }
        .subtitle {
            font-size: 16pt;
            margin-bottom: 40px;
        }
        
        .presented-to {
            font-size: 14pt;
            margin-bottom: 10px;
            font-style: italic;
        }

        .student-name {
            font-size: 32pt;
            font-weight: bold;
            margin: 20px 0;
            border-bottom: 2px solid #cfa158; /* Línea debajo del nombre */
            display: inline-block;
            padding-bottom: 10px;
            min-width: 50%;
        }

        .course-text {
            font-size: 16pt;
            margin-top: 20px;
            line-height: 1.5;
        }
        .course-name {
            font-weight: bold;
            font-size: 20pt;
        }

        .date-text {
            margin-top: 40px;
            font-size: 14pt;
            color: #666;
        }

        /* Sección de Firmas (Usando tablas para DomPDF es más seguro que flexbox) */
        .signatures-table {
            width: 100%;
            margin-top: 80px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 40px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 10px;
            font-weight: bold;
        }
        .signature-image {
            height: 60px; /* Ajusta altura de la firma */
            margin-bottom: -10px; /* Para que se "pose" sobre la línea */
        }
        .signee-title {
            font-size: 10pt;
            font-weight: normal;
        }

    </style>
</head>
<body>
    <div class="container">
        
        {{-- 1. Logo de la Institución (Dinámico por sesión) --}}
        <div class="top-logo">
            @if($institution_logo)
                {{-- IMPORTANTE: Usar public_path para imágenes locales en PDF --}}
                <img src="{{ public_path('storage/' . $institution_logo) }}" alt="Logo Institución">
            @else
                {{-- Placeholder o solo texto si no hay logo --}}
                <h2>{{ $institution_name }}</h2>
            @endif
        </div>

        {{-- 2. Textos Principales --}}
        <h1 class="main-title">Certificado de Finalización</h1>
        <div class="subtitle">Este documento certifica que</div>

        {{-- 3. Nombre del Estudiante --}}
        <div class="student-name">
            {{ $user->name }} {{ $user->last_name }}
        </div>

        {{-- 4. Detalles del Curso --}}
        <div class="course-text">
            ha completado satisfactoriamente el curso de:
            <br>
            <span class="course-name">{{ $course->title }}</span>
            {{-- Aquí podrías agregar la calificación si la tienes --}}
            <br> con una calificación final de: {{ $score }}
        </div>

        {{-- 5. Fecha --}}
        <div class="date-text">
            Expedido el: {{ $date }}
        </div>

        {{-- 6. Firmas (Dinámicas del curso) --}}
        <table class="signatures-table">
            <tr>
                {{-- Firma 1 --}}
                <td>
                    @if($course->cert_sig_1_path)
                        <img src="{{ public_path('storage/' . $course->cert_sig_1_path) }}" class="signature-image" alt="Firma 1">
                    @endif
                    <div class="signature-line">
                        {{ $course->cert_sig_1_name ?? 'Firma Autorizada' }}
                        <br>
                        <span class="signee-title">Instructor / Director</span>
                    </div>
                </td>
                
                {{-- Firma 2 (Opcional) --}}
                <td>
                     @if($course->cert_sig_2_path)
                        <img src="{{ public_path('storage/' . $course->cert_sig_2_path) }}" class="signature-image" alt="Firma 2">
                     @endif
                     <div class="signature-line">
                         {{ $course->cert_sig_2_name ?? 'Validación Académica' }}
                        <br>
                        <span class="signee-title">Control Escolar</span>
                     </div>
                </td>
            </tr>
        </table>

    </div>
</body>
</html>