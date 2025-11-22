<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $course->title }}</title>
    <style>
        body {
            font-family: 'Georgia', serif;
            background-color: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px;
            margin: 0;
        }
        .certificate-container {
            background-color: white;
            width: 800px; /* Ancho fijo para simular hoja */
            height: 600px;
            padding: 50px;
            border: 10px solid #e69a37; /* Color dorado/naranja de tu marca */
            text-align: center;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            max-width: 150px;
            margin-bottom: 30px;
        }
        h1 {
            color: #333;
            font-size: 3em;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .subtitle {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 40px;
        }
        .student-name {
            font-size: 2.5em;
            color: #e69a37;
            font-weight: bold;
            border-bottom: 2px solid #eee;
            display: inline-block;
            padding: 0 40px 10px 40px;
            margin-bottom: 30px;
        }
        .course-text {
            font-size: 1.4em;
            line-height: 1.6;
            color: #444;
        }
        .course-title {
            font-weight: bold;
            font-size: 1.1em;
        }
        .meta-data {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 0 50px;
            font-size: 1em;
            color: #777;
        }
        .score-box {
            font-weight: bold;
            border: 2px solid #e69a37;
            padding: 10px 20px;
            border-radius: 5px;
            color: #e69a37;
        }

        /* Botón flotante para imprimir (no saldrá en la impresión) */
        .btn-print {
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-print:hover { background-color: #555; }

        @media print {
            body { background: none; padding: 0; }
            .certificate-container { box-shadow: none; border: 5px solid #e69a37; width: 100%; height: 100vh; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="certificate-container">
        {{-- Si tienes el logo en public/images/logos/... --}}
        {{-- <img src="{{ asset('images/logos/Universidad Mundo Imperial.png') }}" alt="Logo" class="logo"> --}}
        <div style="height: 50px;"></div> {{-- Espaciador si no hay logo --}}

        <h1>Constancia</h1>
        <p class="subtitle">La Universidad Mundo Imperial otorga la presente a:</p>

        <div class="student-name">
            {{ $user->name }} {{ $user->last_name }}
        </div>

        <p class="course-text">
            Por haber completado satisfactoriamente el curso:<br>
            <span class="course-title">"{{ $course->title }}"</span>
        </p>

        <div class="meta-data">
            <div>
                Fecha: {{ $date }}
            </div>
            <div class="score-box">
                Calificación: {{ $score }}/100
            </div>
        </div>
        
        <div style="margin-top: 80px; border-top: 1px solid #ccc; width: 200px; margin-left: auto; margin-right: auto;"></div>
        <p style="font-size: 0.9em; color: #999;">Firma Autorizada</p>
    </div>

    <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Guardar como PDF</button>
    <a href="{{ route('course.show', $course) }}" class="btn-print" style="background: #666; margin-left: 10px;">Volver al Curso</a>

</body>
</html>