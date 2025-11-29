@extends('layouts.app')

@section('title', 'Mis Certificados')

@section('content')
@vite(['resources/css/Cursos/certificados.css'])

<div class="container-fluid">
    <div class="header-section">
        <h1>Mis Certificados</h1>
        <p>Historial de cursos completados y aprobados.</p>
    </div>

    @if($certificates->isEmpty())
        <div class="no-certificates">
            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="Sin certificados">
            <h3>Aún no tienes certificados.</h3>
            <p>Completa el examen final de tus cursos para obtenerlos.</p>
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
                        <th class="text-center">Certificados</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($certificates as $cert)
                        @php 
                            $course = $cert->completable->course; 
                        @endphp
                        <tr>
                            <td><strong>{{ $course->title }}</strong></td>
                            <td>{{ $cert->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge-success">
                                    {{ $cert->score }} / 100
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('courses.certificate', $course->id) }}" target="_blank" class="btn-secondary" title="Descargar PDF">
                                     Ver PDF
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
