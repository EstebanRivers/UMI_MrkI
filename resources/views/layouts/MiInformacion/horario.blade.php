@extends('layouts.app')

@section('title', 'Mi Horario - ' . session('active_institution_name'))

{{-- Inyectamos el CSS --}}
@push('styles')
    @vite(['resources/css/MiInformacion/horario.css'])
@endpush

@section('content')
<div class="main-content">
    
    <div class="horario-header">
        <div>
            <div class="horario-page-title">HORARIOS</div>
            <div class="horario-period-subtitle">Agosto 2025 – Febrero 2026</div>
        </div>
        
        <button class="horario-export-btn" onclick="window.print()">
            <i class="fa-solid fa-file-export" style="margin-right: 8px;"></i> Exportar
        </button>
    </div>

    <div class="schedule-wrapper">
        {{-- Encabezados de Días --}}
        <div class="schedule-header">
            <div class="time-header">HORA</div>
            <div class="day-header">Lunes</div>
            <div class="day-header">Martes</div>
            <div class="day-header">Miércoles</div>
            <div class="day-header">Jueves</div>
            <div class="day-header">Viernes</div>
            <div class="day-header">Sábado</div>
            <div class="day-header">Domingo</div>
        </div>
        
        {{-- Cuerpo del Horario (Scrollable) --}}
        <div class="schedule-scroll-container">
            <div class="schedule-body">
                
                {{-- Columna de Horas (Fija) --}}
                <div class="time-labels-column">
                    {{-- Genera las horas de 7am a 9pm --}}
                    @for ($i = 7; $i <= 20; $i++)
                        <div class="time-label">
                            {{ sprintf("%02d:00", $i) }}<br>
                            {{ sprintf("%02d:00", $i+1) }}
                        </div>
                    @endfor
                </div>

                {{-- Celdas del Horario (Ejemplo Estático) --}}
                {{-- NOTA: Aquí tendrás que hacer un bucle inteligente con PHP cuando tengas datos reales --}}
                
                {{-- Fila 7:00 - 8:00 --}}
                <div class="schedule-cell"></div> {{-- Lunes --}}
                <div class="schedule-cell">
                    <div class="class-item">
                        <div class="class-name">Base de Datos II</div>
                        <div class="class-room">Sala A</div>
                    </div>
                </div> {{-- Martes --}}
                <div class="schedule-cell"></div> {{-- Miércoles --}}
                <div class="schedule-cell">
                    <div class="class-item">
                        <div class="class-name">Base de Datos II</div>
                        <div class="class-room">Sala A</div>
                    </div>
                </div> {{-- Jueves --}}
                <div class="schedule-cell"></div> {{-- Viernes --}}
                <div class="schedule-cell"></div> {{-- Sábado --}}
                <div class="schedule-cell"></div> {{-- Domingo --}}

                {{-- Fila 8:00 - 9:00 --}}
                <div class="schedule-cell">
                    <div class="class-item">
                        <div class="class-name">Ing. Software</div>
                        <div class="class-room">Lab 3</div>
                    </div>
                </div>
                <div class="schedule-cell"></div>
                <div class="schedule-cell">
                    <div class="class-item">
                        <div class="class-name">Ing. Software</div>
                        <div class="class-room">Lab 3</div>
                    </div>
                </div>
                {{-- ... (rellena el resto de celdas vacías para completar la fila) ... --}}
                 <div class="schedule-cell"></div><div class="schedule-cell"></div><div class="schedule-cell"></div><div class="schedule-cell"></div>

                {{-- ... (Repite para las demás horas) ... --}}

            </div>
        </div>
    </div>
</div>
@endsection