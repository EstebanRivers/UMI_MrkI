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

    {{-- Formulario para editar el curso --}}
    <form action="{{ route('courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
        @csrf {{-- Token de seguridad de Laravel --}}
        @method('PUT') {{-- Actualizacion --}}

        {{-- Título del Curso --}}
        <div style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600;">Título del Curso</label>
            <input type="text" id="title" name="title" required
                   value="{{ old('title', $course->title) }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Descripción --}}
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">Descripción</label>
            {{-- CAMBIO: Corregido el > extra dentro del textarea --}}
            <textarea id="description" name="description" rows="4" required
                      style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">{{ old('description', $course->description) }}</textarea>
        </div>

        {{-- --- INICIO DE LÓGICA CONDICIONAL --- --}}
        {{-- Muestra campos académicos si el curso es de la Universidad --}}
        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">
        
        @if ($currentInstitution->name == 'Universidad Mundo Imperial')
            
            {{-- Horas y Créditos (Corregido) --}}
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                    <input type="number" name="hours" id="hours" required 
                           value="{{ old('hours', $course->hours) }}"
                           style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
                <div style="flex: 1;">
                    <label for="credits" style="display: block; margin-bottom: 8px; font-weight: 600;">Créditos</label>
                    {{-- CORRECCIÓN: Usaba $course->hours por error --}}
                    <input type="number" name="credits" id="credits" required 
                           value="{{ old('credits', $course->credits) }}" 
                           style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
            </div>

            {{-- Filtro de Carrera --}}
            <div style="margin-bottom: 20px;">
                <label for="career_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Carrera</label>
                <select name="career_id" id="career_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="" disabled>Selecciona la Carrera</option>
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}" 
                            {{-- Pre-selecciona el valor guardado --}}
                            @if(old('career_id', $selectedFilters['career_id']) == $career->id) selected @endif>
                            {{ $career->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
        @else {{-- Si no es la Universidad (Corporativo) --}}

            {{-- Horas --}}
            <div style="margin-bottom: 20px;">
                <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                <input type="number" name="hours" id="hours" required 
                       value="{{ old('hours', $course->hours) }}"
                       style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>
            {{-- Campo de créditos oculto --}}
            <input type="hidden" name="credits" value="0">

            {{-- Filtros de Departamentos y Puestos --}}
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="department_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido a Departamento</label>
                    <select name="department_id" id="department_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                        <option value="" disabled>Selecciona el Departamento</option>
                        @foreach($currentInstitution->departments as $department)
                            <option value="{{ $department->id }}"
                                {{-- Pre-selecciona el valor guardado --}}
                                @if(old('department_id', $selectedFilters['department_id']) == $department->id) selected @endif>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="workstation_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido al Puesto</label>
                    <select name="workstation_id" id="workstation_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;" 
                        {{-- Deshabilitar si no hay departamento seleccionado --}}
                        @if(!old('department_id', $selectedFilters['department_id'])) disabled @endif>
                        
                        <option value="">Todos los Puestos del Departamento</option>
                        {{-- El JS se encargará de rellenar esto --}}
                    </select>
                </div>
            </div>
        @endif
        {{-- --- FIN DE LÓGICA CONDICIONAL --- --}}

        
         {{-- Imagen Actual --}}
        @if ($course->image)
            <div style="margin-bottom: 10px;">
                <label>Imagen Actual:</label>
                <img src="{{ asset('storage/' . $course->image) }}" alt="Imagen del curso" style="max-width: 200px; border-radius: 8px;">
            </div>
        @endif
        
        {{-- Campo para subir una NUEVA imagen --}}
        <div style="margin-bottom: 20px;">
            <label for="image">Cambiar Imagen de Portada (opcional)</label>
            <input type="file" id="image" name="image" accept="image/*" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- --- CAMBIO: AÑADIDO EL MATERIAL DE GUÍA --- --}}
        <div style="margin-bottom: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <label for="guide_material" style="display: block; margin-bottom: 8px; font-weight: 600;">Material de Guía (PDF, Word, PPT)</label>
            
            @if ($course->guide_material_path)
                <div style="margin-bottom: 10px;">
                    <a href="{{ asset('storage/' . $course->guide_material_path) }}" target="_blank" class="btn-secondary" style="text-decoration: none; display: inline-block; padding: 8px 12px; background: #6c757d; color: white; border-radius: 5px;">
                        📎 Ver Guía Actual
                    </a>
                </div>
                <label for="guide_material" style="font-size: 0.9em;">Reemplazar archivo (opcional):</label>
            @endif
            
            <input type="file" id="guide_material" name="guide_material" accept=".pdf,.doc,.docx,.ppt,.pptx"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 5px;">
        </div>
        {{-- --- FIN DEL MATERIAL DE GUÍA --- --}}


        {{-- Botones de Enviar --}}
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

@push('scripts')
<script>

    // 1. Datos pasados desde el controlador
    const departmentWorkstations = @json($departmentWorkstationsMap);
    
    // 2. Referencias a los <select>
    const departmentSelect = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');
    
    // 3. ID del puesto que ya estaba guardado (si existe)
    let selectedWorkstationId = "{{ old('workstation_id', $selectedFilters['workstation_id']) }}";

    // 4. Función para poblar los puestos
    function populateWorkstations(selectedDepartmentId) {
        if (!workstationSelect) return; 

        // Limpiamos opciones anteriores (dejamos la opción "Todos los puestos")
        while (workstationSelect.options.length > 1) {
            workstationSelect.remove(1);
        }

        if (selectedDepartmentId && departmentWorkstations[selectedDepartmentId]) {
            workstationSelect.disabled = false;
            
            const workstations = departmentWorkstations[selectedDepartmentId];
            
            workstations.forEach(function (workstation) {
                const option = new Option(workstation.name, workstation.id);
                // Si este puesto es el que estaba guardado, lo seleccionamos
                if (workstation.id == selectedWorkstationId) {
                    option.selected = true;
                }
                workstationSelect.add(option);
            });
        } else {
            workstationSelect.disabled = true;
        }
    }

    // 5. Escuchar cambios en el <select> de departamento
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            // Importante: Si el usuario cambia el depto, ya no queremos
            // forzar la selección del puesto guardado anteriormente.
            selectedWorkstationId = null; 
            populateWorkstations(this.value);
        });
    }

    // 6. Ejecutar la función una vez al cargar la página
    //    para rellenar los puestos del departamento que ya estaba seleccionado.
    if (departmentSelect) {
        populateWorkstations(departmentSelect.value);
    }
</script>
@endpush