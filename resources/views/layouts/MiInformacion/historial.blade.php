@extends('layouts.app')

@section('title', 'Historial Académico - ' . session('active_institution_name'))

{{-- Inyectamos el CSS limpio --}}
@push('styles')
    @vite(['resources/css/MiInformacion/historial.css'])
@endpush

@section('content')
<div class="main-content">
    
    {{-- HEADER --}}
    <div class="historial-header">
        <div class="historial-titles-container">
            <div class="historial-page-title">HISTORIAL ACADÉMICO</div>
        </div>
        <div class="historial-welcome-container">
            <div class="historial-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
            <div class="historial-action-buttons">
                {{-- Botón con JS --}}
                <button class="historial-btn" onclick="alert('Mostrando retícula...')">
                    <i class="fa-solid fa-book-open" style="margin-right: 5px;"></i> Retícula
                </button>
                {{-- Enlace a otra ruta (cuando la tengas) --}}
                <a href="#" class="historial-btn">
                    <i class="fa-solid fa-file-lines" style="margin-right: 5px;"></i> Boleta
                </a>
            </div>
        </div>
    </div>

    {{-- TARJETA DE INFORMACIÓN --}}
    <div class="historial-info-card">
        <div class="student-info-grid">
            <div class="info-item">
                <span class="info-label">Matrícula:</span>
                <span class="info-value">{{ $user->RFC }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $user->nombre }} {{ $user->apellido_paterno }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Semestre actual:</span>
                <span class="info-value">{{ $user->academicProfile->semestre ?? '1' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Carrera:</span>
                <span class="info-value">{{ $user->academicProfile->carrera ?? 'Ingeniería' }}</span>
            </div>
        </div>
        <div class="specialty-container">
            <div class="specialty-label">Especialidad:</div>
            <div class="specialty-value">Desarrollo de Software</div>
        </div>
    </div>

    {{-- TABLA INTEGRADA --}}
    <div class="historial-table-wrapper">
        
        {{-- Encabezados (Alineados con el contenido derecho) --}}
        <div class="table-header">
            <div class="header-item col-materia">Materia</div>
            <div class="header-item col-creditos">Créditos</div>
            <div class="header-item col-calificacion">Calif.</div>
            <div class="header-item col-evaluacion">Eval.</div>
            <div class="header-item col-observaciones">Observaciones</div>
        </div>

        <div class="materias-scroll-container">
            
            {{-- BLOQUE SEMESTRE 1 (Ejemplo Estático) --}}
            <div class="semester-card">
                {{-- Panel Izquierdo --}}
                <div class="semester-left-panel">
                    <div class="semester-number">1</div>
                    <div class="semester-period">AGO 2020<br>DIC 2020</div>
                    <div class="semester-grade-label">Promedio:</div>
                    <div class="semester-grade-value">96.6</div>
                </div>

                {{-- Panel Derecho (Materias) --}}
                <div class="semester-content">
                    {{-- Fila de Materia --}}
                    <div class="materia-row">
                        <div class="col-materia">Inteligencia de Negocios</div>
                        <div class="cell-divider"></div>
                        <div class="col-creditos">5</div>
                        <div class="cell-divider"></div>
                        <div class="col-calificacion">100</div>
                        <div class="cell-divider"></div>
                        <div class="col-evaluacion">OR</div>
                        <div class="cell-divider"></div>
                        <div class="col-observaciones">-</div>
                    </div>

                    <div class="materia-row">
                        <div class="col-materia">Ética Profesional</div>
                        <div class="cell-divider"></div>
                        <div class="col-creditos">4</div>
                        <div class="cell-divider"></div>
                        <div class="col-calificacion">94</div>
                        <div class="cell-divider"></div>
                        <div class="col-evaluacion">REG</div>
                        <div class="cell-divider"></div>
                        <div class="col-observaciones">Examen 2da op.</div>
                    </div>
                    
                    {{-- Agrega más filas aquí --}}
                </div>
            </div>

             {{-- BLOQUE SEMESTRE 2 (Ejemplo) --}}
             <div class="semester-card">
                <div class="semester-left-panel">
                    <div class="semester-number">2</div>
                    <div class="semester-period">ENE 2021<br>JUN 2021</div>
                    <div class="semester-grade-label">Promedio:</div>
                    <div class="semester-grade-value">92.0</div>
                </div>
                <div class="semester-content">
                    <div class="materia-row">
                        <div class="col-materia">Programación Web</div>
                        <div class="cell-divider"></div>
                        <div class="col-creditos">6</div>
                        <div class="cell-divider"></div>
                        <div class="col-calificacion">98</div>
                        <div class="cell-divider"></div>
                        <div class="col-evaluacion">OR</div>
                        <div class="cell-divider"></div>
                        <div class="col-observaciones">-</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- FOOTER (Calificación Final) --}}
    <div class="historial-footer">
        <div class="final-grade-badge">
            Promedio Final: 95.4
        </div>
        <div class="credits-info">
            <div class="credit-badge">
                Total Créditos: <span class="credit-value">260</span>
            </div>
            <div class="credit-badge">
                Acumulados: <span class="credit-value">180</span>
            </div>
        </div>
    </div>

</div>
@endsection