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
        @csrf
        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

        {{-- Título del Curso --}}
        <div style="margin-bottom: 20px;">
            <label for="title" style="display: block; margin-bottom: 8px; font-weight: 600;">Título del Curso</label>
            <input type="text" id="title" name="title" required value="{{ old('title') }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Descripción --}}
        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; margin-bottom: 8px; font-weight: 600;">Descripción</label>
            <textarea id="description" name="description" rows="4" required
                      style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">{{ old('description') }}</textarea>
        </div>

        {{-- Campos Académicos/Corporativos --}}
        @if ($currentInstitution->name == 'Universidad Mundo Imperial')
            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                    <input type="number" name="hours" id="hours" required value="{{ old('hours') }}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
                <div style="flex: 1;">
                    <label for="credits" style="display: block; margin-bottom: 8px; font-weight: 600;">Créditos</label>
                    <input type="number" name="credits" id="credits" required value="{{ old('credits') }}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="career_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Carrera</label>
                <select name="career_id" id="career_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="" disabled selected>Selecciona la Carrera</option>
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}" {{ old('career_id') == $career->id ? 'selected' : '' }}>{{ $career->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div style="margin-bottom: 20px;">
                <label for="hours" style="display: block; margin-bottom: 8px; font-weight: 600;">Horas</label>
                <input type="number" name="hours" id="hours" required value="{{ old('hours') }}" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div style="flex: 1;">
                    <label for="department_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido a Departamento</label>
                    <select name="department_id" id="department_id" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
                        <option value="" disabled selected>Selecciona el Departamento</option>
                        @foreach($currentInstitution->departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="workstation_id" style="display: block; margin-bottom: 8px; font-weight: 600;">Dirigido al Puesto</label>
                    <select name="workstation_id" id="workstation_id" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;" disabled>
                        <option value="" selected>Primero selecciona un departamento</option>
                        <option value="">Todos los Puestos del Departamento</option>
                    </select>
                </div>
            </div>
        @endif
        
        <div style="margin-bottom: 30px;">
            <label for="image" style="display: block; margin-bottom: 8px; font-weight: 600;">Imagen del Curso (opcional)</label>
            <input type="file" id="image" name="image" accept="image/*"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>
        <div style="margin-bottom: 30px;">
            <label for="guide_material" style="display: block; margin-bottom: 8px; font-weight: 600;">Material de Guía (PDF, Word, PPT)</label>
            <input type="file" id="guide_material" name="guide_material" accept=".pdf,.doc,.docx,.ppt,.pptx"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>

        {{-- Campos para Certificado --}}
        <h2 style="font-size: 22px; margin-bottom: 20px; color: #333;">Configuración del Certificado</h2>
        <div style="margin-bottom: 20px;">
            <label for="cert_bg_image" style="display: block; margin-bottom: 8px; font-weight: 600;">Imagen de Fondo del Certificado (opcional)</label>
            <input type="file" id="cert_bg_image" name="cert_bg_image" accept="image/*"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
        </div>
        <div style="margin-bottom: 20px;">
            <label for="cert_sig_1_image" style="display: block; margin-bottom: 8px; font-weight: 600;">Firma 1 del Certificado (PNG, opcional)</label>
            <input type="file" id="cert_sig_1_image" name="cert_sig_1_image" accept="image/png"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            <input type="text" name="cert_sig_1_name" placeholder="Nombre/Cargo de la Firma 1" value="{{ old('cert_sig_1_name') }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 10px;">
        </div>
        <div style="margin-bottom: 30px;">
            <label for="cert_sig_2_image" style="display: block; margin-bottom: 8px; font-weight: 600;">Firma 2 del Certificado (PNG, opcional)</label>
            <input type="file" id="cert_sig_2_image" name="cert_sig_2_image" accept="image/png"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc;">
            <input type="text" name="cert_sig_2_name" placeholder="Nombre/Cargo de la Firma 2" value="{{ old('cert_sig_2_name') }}"
                   style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 10px;">
        </div>

        <button type="submit"
                style="background: #e69a37; color: white; padding: 14px 28px; border: none; border-radius: 12px; cursor: pointer; font-weight: 600; font-size: 16px;">
            Guardar Curso
        </button>
    </form>
</div>

{{-- 
    IMPORTANTE: El script está DENTRO de @section('content') y SIN @push.
    Esto asegura que el script se ejecute cada vez que el contenido se carga vía AJAX.
--}}
<script>
    (function() {
        // 1. Obtenemos los datos que pasamos desde el controlador
        const departmentWorkstations = @json($departmentWorkstationsMap);

        // 2. Referencias a los <select>
        const departmentSelect = document.getElementById('department_id');
        const workstationSelect = document.getElementById('workstation_id');

        // 3. Función para poblar los puestos
        function populateWorkstations(selectedDepartmentId) {
            if (!workstationSelect) return;

            // Limpiamos las opciones (reconstrucción limpia)
            workstationSelect.innerHTML = '';

            // Opción por defecto
            const defaultOption = document.createElement('option');
            defaultOption.value = "";
            defaultOption.selected = true;

            if (selectedDepartmentId && departmentWorkstations[selectedDepartmentId]) {
                workstationSelect.disabled = false;
                defaultOption.textContent = 'Selecciona el Puesto (Opcional)';
                workstationSelect.appendChild(defaultOption);
                
                // Opción para "Todos"
                const allOption = document.createElement('option');
                allOption.value = ""; 
                allOption.textContent = "Todos los Puestos del Departamento";
                workstationSelect.appendChild(allOption);

                const workstations = departmentWorkstations[selectedDepartmentId];
                
                workstations.forEach(function (workstation) {
                    const option = document.createElement('option');
                    option.value = workstation.id;
                    option.textContent = workstation.name;
                    workstationSelect.appendChild(option);
                });

            } else {
                workstationSelect.disabled = true;
                defaultOption.textContent = 'Primero selecciona un departamento';
                workstationSelect.appendChild(defaultOption);
            }
        }

        // 4. Lógica de Inicialización
        if (departmentSelect) {
            // Escuchar cambios
            departmentSelect.addEventListener('change', function (e) {
                populateWorkstations(this.value);
            });

            // Si ya hay un valor seleccionado (ej. al volver de un error de validación o edición)
            if (departmentSelect.value) {
                populateWorkstations(departmentSelect.value);
                
                // Intentar restaurar el puesto seleccionado si existe
                const oldWorkstation = "{{ old('workstation_id') }}";
                if(oldWorkstation && workstationSelect) {
                    setTimeout(() => { workstationSelect.value = oldWorkstation; }, 0);
                }
            }
        }
    })();
</script>

@endsection