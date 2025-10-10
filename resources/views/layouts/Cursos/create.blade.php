@extends('layouts.app')

@section('title', 'Crear Nuevo Curso - ' . session('active_institution_name'))

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding: 20px; background-color: #ECF0F1; border-radius: 12px;">
    <h1 style="color: #333; margin-bottom: 30px; font-size: 28px;">Crear Nuevo Curso</h1>

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
    <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf {{-- Token de seguridad de Laravel --}}

        {{-- Título del Curso --}}
        <div style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600;">Título del Curso</label>
            <input type="text" id="title" name="title" required
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Descripción --}}
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">Descripción</label>
            <textarea id="description" name="description" rows="4" required
                      style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;"></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="institution_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Unidad de Negocio</label>
            <select name="institution_id" id="institution_id" required style=" padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                <option value="" disabled selected>Selecciona la insitucion...</option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}">{{ $institution->name }}</option>
                @endforeach
            </select >
        </div>

        {{-- Créditos y Horas --}}
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label for="credits" style="display: block; margin-bottom: 8px; font-weight: 600;">Créditos</label>
                <input type="number" id="credits" name="credits" required value="10"
                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
            <div style="flex: 1;">
                <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                <input type="number" id="hours" name="hours" required value="40"
                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
        </div>
        
        {{-- Imagen del curso --}}
        <div style="margin-bottom: 30px;">
            <label for="image" style="display: block; margin-bottom: 8px; font-weight: 600;">Imagen del Curso (opcional)</label>
            <input type="file" id="image" name="image" accept="image/*"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Botón de Enviar --}}
        <button type="submit"
                style="background: #e69a37; color: white; padding: 14px 28px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 16px;">
            Guardar Curso
        </button>
    </form>
</div>
@endsection