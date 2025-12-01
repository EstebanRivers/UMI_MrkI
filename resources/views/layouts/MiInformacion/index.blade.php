@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@push('styles')
    @vite(['resources/css/MiInformacion/profile.css'])
@endpush

@section('content')
@php
   
    $isStudent = $user->hasActiveRole('estudiante');  
    $isStaff = !$isStudent; 
    $addr = $user->address; 
@endphp

<div class="main-content">
    
   
    <div class="profile-header">
        <div class="profile-page-title">PERFIL</div>
      
    </div>
    
   
    <div class="profile-welcome-container">
        <img src="{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : asset('images/LOGO 4.svg') }}" class="profile-welcome-image" alt="Foto de perfil">
        <div class="profile-orange-line"></div>
        <div class="profile-student-name">
            {{ $user->nombre }} {{ $user->apellido_paterno }} {{ $user->apellido_materno }}
        </div>
    </div>
    
    
    <div class="profile-info-section">

        
        @if($isStaff)
            <div class="profile-info-row">
                <span class="profile-info-title">Departamento:</span>
                <span class="profile-info-content">{{ $user->department->name ?? 'No asignado' }}</span>
            </div>
            <div class="profile-info-row">
                <span class="profile-info-title">Puesto:</span>
                <span class="profile-info-content">{{ $user->workstation->name ?? 'No asignado' }}</span>
            </div>
            <div class="profile-divider"></div>
        @endif

       
        @if($isStudent && $user->academicProfile)
            <div class="profile-info-row">
                <span class="profile-info-title">Carrera:</span>
                <span class="profile-info-content">{{ $user->academicProfile->carrera ?? 'No asignada' }}</span>
            </div>
            <div class="profile-inline-group">
                 <div class="profile-inline-pair">
                    <span class="profile-info-title">Semestre:</span>
                    <span class="profile-info-content">{{ $user->academicProfile->semestre ?? 'N/A' }}</span>
                </div>
                 <div class="profile-inline-pair">
                    <span class="profile-info-title">Especialidad:</span>
                    <span class="profile-info-content">Ingeniería de Software</span>
                </div>
            </div>
            <div class="profile-divider"></div>
        @endif
        
        
        <div class="profile-inline-group">
            <div class="profile-inline-pair">
                
                <span class="profile-info-title">{{ $isStudent ? 'Matrícula:' : 'RFC/Usuario:' }}</span>
                <span class="profile-info-content">{{ $user->RFC }}</span>
            </div>
             <div class="profile-inline-pair">
                <span class="profile-info-title">Correo:</span>
                <span class="profile-info-content">{{ $user->email }}</span>
            </div>
        </div>

        <div class="profile-tight-group" style="margin-top: 10px;">
            <div class="profile-tight-pair">
                <span class="profile-info-title">Teléfono:</span>
                <span class="profile-info-content">{{ $user->telefono ?? 'Sin registro' }}</span>
            </div>
          
             @if($isStudent)
             <div class="profile-inline-group">
                <div class="profile-inline-pair">
                    <span class="profile-info-title">Edad:</span>
                    <span class="profile-info-content">{{ $user->edad ?? 'Sin registro' }}</span>
                </div>
                 <div class="profile-inline-pair">
                    <span class="profile-info-title">Fecha de Nacimiento:</span>
                    <span class="profile-info-content">
                        {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : 'Sin registro' }}
                    </span>
                </div>
            </div>
            
            <div class="profile-tight-pair">
                <span class="profile-info-title">CURP:</span>
                <span class="profile-info-content">{{ $user->curp ?? 'No registrado' }}</span>
            </div>
        </div>
        @endif

       
        @if($isStudent)
            <div class="profile-divider"></div>
            <div class="profile-info-row">
                <span class="profile-info-title">Dirección:</span>
            </div>
            
            <div class="profile-info-row">
                <span class="profile-info-title">Colonia:</span>
                <span class="profile-info-content">{{ $addr->colonia ?? 'N/A' }}</span>
            </div>
            
            <div class="profile-info-row">
                <span class="profile-info-title">Calle:</span>
                <span class="profile-info-content">{{ $addr->calle ?? 'N/A' }}</span>
            </div>
            
            <div class="profile-inline-group">
                <div class="profile-inline-pair">
                    <span class="profile-info-title">Ciudad:</span>
                    <span class="profile-info-content">{{ $addr->ciudad ?? 'N/A' }}</span>
                </div>
                <div class="profile-inline-pair">
                    <span class="profile-info-title">Estado:</span>
                    <span class="profile-info-content">{{ $addr->estado ?? 'N/A' }}</span>
                </div>
            </div>
             <div class="profile-info-row">
                <span class="profile-info-title">C.P.:</span>
                <span class="profile-info-content">{{ $addr->cp ?? 'N/A' }}</span>
            </div>
        @endif

    </div>
</div>
@endsection