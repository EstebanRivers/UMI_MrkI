{{-- en resources/views/layouts/Cursos/certificates_list.blade.php --}}

@extends('layouts.app')

@section('title', 'Mis Certificados')

@section('content')
@vite(['resources/css/Cursos/certificados.css'])

<div class="container-fluid">
    
    {{-- ENCABEZADO CONTEXTUAL --}}
    <div class="header-section" style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
        
        {{-- Logo de la Institución Activa --}}
        @if(session('active_institution_logo'))
            <img src="{{ asset('storage/' . session('active_institution_logo')) }}" 
                 alt="Logo Institución" 
                 style="height: 60px; width: auto;">
        @endif

        <div>
            <h1 style="margin: 0; color: #2c3e50;">Mis Certificados</h1>
            <p style="margin: 5px 0 0; color: #7f8c8d;">
                Mostrando historial de: <strong>{{ session('active_institution_name') }}</strong>
            </p>
        </div>
    </div>

    @if($certificates->isEmpty())
        <div class="no-certificates">
            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="Sin certificados">
            <h3>No tienes certificados en esta institución.</h3>
            <p>Si has completado cursos en otra unidad de negocio, cambia tu contexto en el menú superior.</p>
            <a href="{{ route('Cursos.index') }}" class="btn-primary">Ir a Cursos</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Fecha de Obtención</th>
                        <th>Calificación</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificates as $cert)
                        @php 
                            $course = $cert->completable->course; 
                        @endphp
                        <tr>
                            <td style="font-weight: 600;">{{ $course->title }}</td>
                            <td>{{ $cert->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge-success" style="background: #e8f5e9; color: #2e7d32; padding: 5px 10px; border-radius: 15px; font-weight: bold;">
                                    {{ $cert->score }} / 100
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('courses.certificate', $course->id) }}" target="_blank" class="btn-secondary" title="Ver PDF">
                                     <i class="fa-solid fa-file-pdf"></i> Ver Certificado
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection