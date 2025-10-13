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
        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

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

        {{-- Verifica si la institución es la Universidad para mostrar campos académicos --}}
        @if ($currentInstitution->name == 'Universidad Mundo Imperial')

            {{-- Horas y Créditos (lado a lado) --}}
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                    <input type="number" name="hours" id="hours" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
                <div style="flex: 1;">
                    <label for="credits" style="display: block; margin-bottom: 8px; font-weight: 600;">Créditos</label>
                    <input type="number" name="credits" id="credits" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
            </div>

            {{-- Carrera --}}
            <div style="margin-bottom: 20px;">
                <label for="career_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Carrera</label>
                <select name="career_id" id="career_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="" disabled selected>Selecciona la Carrera</option>
                    {{-- Itera directamente sobre las carreras de la institución actual --}}
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}">{{ $career->name }}</option>
                    @endforeach
                </select>
            </div>

        @else {{-- Si no es la Universidad, muestra los campos corporativos --}}

            {{-- Horas (ocupa todo el ancho) --}}
            <div style="margin-bottom: 20px;">
                <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                <input type="number" name="hours" id="hours" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            {{-- Departamentos y Puestos --}}
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="department_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido a Departamento</label>
                    <select name="department_id" id="department_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                        <option value="" disabled selected>Selecciona el Departamento</option>
                        {{-- Itera sobre los departamentos de la institución actual --}}
                        @foreach($currentInstitution->departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="workstation_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido al Puesto</label>
                    <select name="workstation_id" id="workstation_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                        <option value="" disabled selected>Selecciona el Puesto de trabajo</option>
                        {{-- Itera anidando sobre los puestos de cada departamento --}}
                        @foreach($currentInstitution->departments as $department)
                            @foreach($department->workstations as $workstation)
                                <option value="{{ $workstation->id }}">{{ $workstation->name }} ({{ $department->name }})</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
        
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