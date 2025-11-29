@extends('layouts.app')

@section('title', 'Mis Clases - ' . session('active_institution_name'))

{{-- Inyectamos el CSS específico --}}
@push('styles')
    @vite(['resources/css/MiInformacion/clases.css'])
@endpush

@section('content')
<div class="main-content">
    
    <div class="clases-header">
        <div class="clases-header-top">
            <div class="clases-titles-container">
                <div class="clases-page-title">CLASES</div>
                {{-- (Aquí podrías hacer dinámico el periodo si lo tuvieras en la sesión) --}}
                <div class="clases-period-subtitle">Agosto 2025 – Febrero 2026</div>
            </div>
            
            <div class="clases-welcome-container">
                <div class="clases-welcome-message">¡Bienvenido(a) {{ $user->nombre }}!</div>
                
                {{-- Botón de Tareas (Si aún no tienes la ruta, deja el #) --}}
                <a href="#" class="clases-tasks-section">
                    <div class="clases-tasks-icon">
                        <img src="{{ asset('images/icons/clipboard-check-solid.svg') }}" alt="Tareas">
                    </div>
                    <div class="clases-tasks-text">Tareas</div>
                </a>
            </div>
        </div>
    </div>

    <div class="clases-container">
        <div class="clases-grid">
            
            {{-- BUCLE REAL: Muestra las clases de la base de datos --}}
            {{-- (Si $clases está vacío, mostrará el bloque @empty) --}}
            @forelse($clases as $clase)
                <div class="class-card">
                    <div class="class-icon-container">
                        {{-- Intenta cargar la imagen de la materia, o usa una por defecto --}}
                        <img src="{{ asset('images/' . ($clase->icono ?? 'default-class.svg')) }}" alt="Ícono">
                    </div>
                    <div class="class-content">
                        <div class="class-title">{{ $clase->nombre }}</div>
                        <div class="class-orange-line"></div>
                        <div class="class-footer">
                            {{-- Botones de acción con Font Awesome --}}
                            <div class="class-icon-placeholder" title="Ver detalles">
                                <i class="fa-solid fa-eye"></i>
                            </div>
                            <div class="class-icon-placeholder" title="Agregar">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Si no hay clases reales, mostramos el mensaje --}}
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <p>No tienes clases asignadas en este periodo.</p>
                </div>
            @endforelse

        </div>
        
        <a href="#" class="previous-classes-btn">Clases anteriores</a>
    </div>
</div>
@endsection