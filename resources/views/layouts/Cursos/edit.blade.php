@extends('layouts.app')

@section('title', 'Editar Curso - ' .$course->title) {{-- Titulo Dinamico --}}

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px; background-color: #ECF0F1; border-radius: 14px;">
    <h1 style="color: #333; margin-bottom: 30px; font-size: 28px;">Editar Curso</h1>

    @if ($errors->any())
    <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <strong>¡Ups! Hubo algunos problemas con tu entrada.</strong>
        <ul style="margin-top: 10px; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Formulario para crear el curso --}}
    <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
        @csrf {{-- Token de seguridad de Laravel --}}
        @method('PUT') {{-- Actualizacion --}}

        {{-- Título del Curso --}}
        <div style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600;">Título del Curso</label>
            <input type="text" id="title" name="title" required
                    value="{{old ('title', $course->title)}}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Descripción --}}
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">Descripción</label>
            <textarea id="description" name="description" rows="4" required
                      style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                    >{{ old('description', $course->description) }}</textarea>
        </div>

        {{-- Créditos y Horas --}}
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label for="credits" style="display: block; margin-bottom: 8px; font-weight: 600;">Créditos</label>
                <input type="number" id="credits" name="credits" required
                        value="{{ old('hours', $course->hours) }}"
                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
            <div style="flex: 1;">
                <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                <input type="number" id="hours" name="hours" required
                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
        </div>

        
         {{-- Imagen Actual --}}
        @if ($course->image)
            <div style="margin-bottom: 10px;">
                <label>Imagen Actual:</label>
                <img src="{{ asset('storage/' . $course->image) }}" alt="Imagen del curso" style="max-width: 200px; border-radius: 8px;">
            </div>
        @endif
        
        {{-- Campo para subir una NUEVA imagen --}}
        <div style="margin-bottom: 30px;">
            <label for="image">Cambiar Imagen de Portada (opcional)</label>
            <input type="file" id="image" name="image" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Botón de Enviar --}}
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid #eee; padding-top: 20px;">
            <button type="submit" name="action" value="save_and_exit"
                    style="background: #6c757d; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Guardar Cambios
            </button>
            <button type="submit" name="action" value="save_and_continue"
                    style="background: #e69a37; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Guardar y Editar Temas &rarr;
            </button>
        </div>
    </form>
</div>
@endsection