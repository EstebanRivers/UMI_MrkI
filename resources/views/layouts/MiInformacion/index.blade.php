@extends('layouts.app')

@section('title', 'Mi Información - UHTA')

@section('content')

  <!-- Header -->
<div class="profile-header">
        <h1 class="profile-title">PERFIL</h1>
        <p class="profile-welcome">¡Bienvenido(a) {{ Auth::user()->nombre }}!</p>
    </div>
<div class="profile-container">

    <!-- Contenedor principal del perfil -->
    <div class="profile-card">
        
        <!-- Sección superior con icono y nombre -->
        <div class="profile-top-section">
            <!-- Icono de perfil -->
            <div class="profile-avatar">
                <div class="profile-icon">
                    <img src="{{ asset('images/icons/circle-user-solid-full.svg') }}" alt="Icono de usuario">
                </div>
            </div>
            
            <!-- Nombre completo -->
            <h2 class="profile-name">
                {{ Auth::user()->nombre }}
            </h2>
        </div>

        <!-- Información de tipo de usuario -->
        <div class="profile-details">
            <div class="profile-grid">
                
                <!-- Carrera -->
                <div class="profile-grid-full">
                    <div class="profile-field">
                        <span class="profile-label">Carrera:</span>
                        <span class="profile-value">
                            {{ Auth::user()->carrera ?: 'No especificada' }}
                        </span>
                    </div>
                </div>

                <!-- Matrícula -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">Matrícula:</span>
                        <span class="profile-value">
                            {{ Auth::user()->matricula ?: 'No asignada' }}
                        </span>
                    </div>
                </div>

                <!-- Semestre -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">Semestre:</span>
                        <span class="profile-value">
                            {{ Auth::user()->semestre ?: 'No especificado' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Información de general -->
            <div class="profile-section">
                <div class="profile-grid">
                    <!-- Correo -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">Correo:</span>
                        <span class="profile-value">
                            {{ Auth::user()->email }}
                        </span>
                    </div>
                </div>

                <!-- Teléfono -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">Teléfono:</span>
                        <span class="profile-value">
                            {{ Auth::user()->telefono ?: 'No especificado' }}
                        </span>
                    </div>
                </div>

                <!-- CURP -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">CURP:</span>
                        <span class="profile-value">
                            {{ Auth::user()->curp ?: 'No especificado' }}
                        </span>
                    </div>
                </div>

                <!-- Edad -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label">Edad:</span>
                        <span class="profile-value">
                            @if(Auth::user()->edad)
                                {{ Auth::user()->edad }} años
                            @elseif(Auth::user()->fecha_nacimiento)
                                {{ \Carbon\Carbon::parse(Auth::user()->fecha_nacimiento)->age }} años
                            @else
                                No especificada
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Fecha de nacimiento -->
                <div>
                    <div class="profile-field">
                        <span class="profile-label profile-label-wide">Fecha de nacimiento:</span>
                        <span class="profile-value">
                            {{ Auth::user()->fecha_nacimiento ? \Carbon\Carbon::parse(Auth::user()->fecha_nacimiento)->format('d/m/Y') : 'No especificada' }}
                        </span>
                    </div>
                </div>
                    
                    <!-- Colonia -->
                    <div>
                        <div class="profile-field">
                            <span class="profile-label">Colonia:</span>
                            <span class="profile-value">
                                {{ Auth::user()->colonia ?: 'No especificada' }}
                            </span>
                        </div>
                    </div>

                    <!-- Calle -->
                    <div>
                        <div class="profile-field">
                            <span class="profile-label">Calle:</span>
                            <span class="profile-value">
                                {{ Auth::user()->calle ?: 'No especificada' }}
                            </span>
                        </div>
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <div class="profile-field">
                            <span class="profile-label">Ciudad:</span>
                            <span class="profile-value">
                                {{ Auth::user()->ciudad ?: 'No especificada' }}
                            </span>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <div class="profile-field">
                            <span class="profile-label">Estado:</span>
                            <span class="profile-value">
                                {{ Auth::user()->estado ?: 'No especificado' }}
                            </span>
                        </div>
                    </div>

                    <!-- Código Postal -->
                    <div>
                        <div class="profile-field">
                            <span class="profile-label">C.P.:</span>
                            <span class="profile-value">
                                {{ Auth::user()->codigo_postal ?: 'No especificado' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional del sistema -->
            <div class="profile-system-info">
                <div class="profile-system-grid">
                    <div>
                        <span class="profile-system-text">
                            <strong>Roles asignados:</strong>
                            @foreach(Auth::user()->roles as $role)
                                <span class="profile-role-badge">
                                    {{ $role->display_name }}
                                </span>
                            @endforeach
                        </span>
                    </div>
                    <div class="profile-system-right">
                        <span class="profile-system-text">
                            <strong>Miembro desde:</strong> {{ Auth::user()->created_at->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection