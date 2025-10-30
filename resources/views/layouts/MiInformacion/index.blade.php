@extends('layouts.app')

{{-- Usamos $page_title que viene del controlador (ej: 'PERFIL' o 'CLASES') --}}
@section('title', $page_title . ' - Mi Información')

@section('content')

    {{-- 
        🚩 ESTA LÍNEA ES LA CLAVE PARA LA CARGA DINÁMICA
        El controlador le dice a la vista qué archivo incluir ($subview)
        y este archivo incluye ese contenido (ej. partials.clases).
    --}}
    @if(isset($subview) && View::exists($subview))
        @include($subview)
    @else
        <div class="container">
            {{-- Muestra un error si la vista parcial no existe --}}
            <div class="alert alert-danger">Error: Contenido de la sección '{{ $seccion ?? 'solicitada' }}' no encontrado. Asegúrate de que el archivo '{{ $subview ?? '' }}.blade.php' exista.</div>
        </div>
    @endif

@endsection