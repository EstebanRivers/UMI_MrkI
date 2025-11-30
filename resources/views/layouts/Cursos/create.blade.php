@extends('layouts.app')

@section('title', 'Crear Nuevo Curso - ' . session('active_institution_name'))


@section('content')
@vite(['resources/css/Cursos/createCourses.css']) 

<div class="create-course-container">
    <h1 class="page-title">Crear Nuevo Curso</h1>

    @if ($errors->any())
        <div class="alert-error">
            <strong>¡Ups! Hubo algunos problemas con tu entrada.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="institution_id" value="{{ $currentInstitution->id }}">

        {{-- Título --}}
        <div class="form-group">
            <label for="title">Título del Curso</label>
            <input type="text" id="title" name="title" required value="{{ old('title') }}">
        </div>

        {{-- Descripción --}}
        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
        </div>

        {{-- Campos especiales para Universidad Mundo Imperial --}}
        @if ($currentInstitution->name == 'Universidad Mundo Imperial')
            <div class="form-row">
                <div class="form-group flex-1">
                    <label for="hours">Horas</label>
                    <input type="number" name="hours" id="hours" required value="{{ old('hours') }}">
                </div>

                <div class="form-group flex-1">
                    <label for="credits">Créditos</label>
                    <input type="number" name="credits" id="credits" required value="{{ old('credits') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="career_id">Carrera</label>
                <select name="career_id" id="career_id" required>
                    <option value="" disabled selected>Selecciona la Carrera</option>
                    @foreach($currentInstitution->careers as $career)
                        <option value="{{ $career->id }}" {{ old('career_id') == $career->id ? 'selected' : '' }}>
                            {{ $career->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        @else
            <div class="form-group">
                <label for="hours">Horas</label>
                <input type="number" name="hours" id="hours" required value="{{ old('hours') }}">
            </div>

            <div class="form-row">
                <div class="form-group flex-1">
                    <label for="department_id">Dirigido a Departamento</label>
                    <select name="department_id" id="department_id" required>
                        <option value="" disabled selected>Selecciona el Departamento</option>
                        @foreach($currentInstitution->departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group flex-1">
                    <label for="workstation_id">Dirigido al Puesto</label>
                    <select name="workstation_id" id="workstation_id" disabled>
                        <option value="" selected>Primero selecciona un departamento</option>
                        <option value="">Todos los Puestos del Departamento</option>
                    </select>
                </div>
            </div>
        @endif

        <div class="form-row">
            <div class="form-group" >
                <label class="file-upload-label" for="image">Imagen del Curso</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p id="image-name-image" class="file-name"></p>
            </div>

            <div class="form-group" >
                <label class="file-upload-label" for="guide_material">Material de Guía (PDF, Word, PPT)</label>
                <input type="file" id="guide_material" name="guide_material" accept=".pdf,.doc,.docx,.ppt,.pptx">
                <p id="image-name-guide" class="file-name"></p>
            </div>
        </div>

        {{-- Certificado --}}
        <h2 class="section-title">Configuración del Certificado</h2>
        <div class="form-group">
            <label class="file-upload-label" for="cert_bg_image">Imagen de Fondo </label>
            <input type="file" id="cert_bg_image" name="cert_bg_image" accept="image/*">
            <p id="image-name-bg" class="file-name"></p>
        </div>
        <div class="form-row" >
            <div class="form-group">
                <label class="file-upload-label"for="cert_sig_1_image">Firma 1 (Subir imagen de la Firma 1)</label>
                <input type="file" id="cert_sig_1_image" name="cert_sig_1_image" accept="image/png" >
                <input type="text" name="cert_sig_1_name" placeholder="Nombre/Cargo de la Firma 1"  value="{{ old('cert_sig_1_name') }}">
                <p id="image-name-sig1" class="file-name"></p>
            </div>

            <div class="form-group">
                <label class="file-upload-label"for="cert_sig_2_image">Firma 2 (Subir imagen de la Firma 2)</label>
                <input type="file" id="cert_sig_2_image" name="cert_sig_2_image" accept="image/png" >
                <input type="text" name="cert_sig_2_name" placeholder="Nombre/Cargo de la Firma 2"  value="{{ old('cert_sig_2_name') }}">
                <p id="image-name-sig2" class="file-name"></p>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Guardar Curso
        </button>
    </form>
</div>

<script>
(function() {

    /* ============================
       1. Datos enviados desde PHP
    ============================ */
    const departmentWorkstations = @json($departmentWorkstationsMap);

    /* ============================
       2. Selects del formulario
    ============================ */
    const departmentSelect  = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');
    const imageInput        = document.getElementById('image');
    const imageNameLabel    = document.getElementById('image-name');

    /* ============================
       3. Función para cargar puestos
    ============================ */
    function populateWorkstations(departmentId) {
        if (!workstationSelect) return;

        workstationSelect.innerHTML = ''; // limpiamos
        const defaultOption = new Option('', '', true, true);

        if (departmentId && departmentWorkstations[departmentId]) {

            workstationSelect.disabled = false;
            defaultOption.textContent = 'Selecciona el Puesto (Opcional)';
            workstationSelect.appendChild(defaultOption);

            // Opción para todos
            workstationSelect.appendChild(new Option("Todos los Puestos del Departamento", ""));

            // Recorrer puestos y agregarlos
            departmentWorkstations[departmentId].forEach(w => {
                workstationSelect.appendChild(new Option(w.name, w.id));
            });

        } else {
            workstationSelect.disabled = true;
            defaultOption.textContent = 'Primero selecciona un departamento';
            workstationSelect.appendChild(defaultOption);
        }
    }

    /* ============================
       4. Inicializador 
    ============================ */
    if (departmentSelect) {

        departmentSelect.addEventListener('change', e => populateWorkstations(e.target.value));

        if (departmentSelect.value) {
            populateWorkstations(departmentSelect.value);

            // Restaurar valor anterior si existe
            const oldWorkstation = "{{ old('workstation_id') }}";
            if (oldWorkstation) workstationSelect.value = oldWorkstation;
        }
    }

    /* ============================
       5. Mostrar nombre de archivo
    ============================ */
    /* Mostrar nombres de los archivos */
    const fileInputs = [
        { input: 'image',             label: 'image-name-image' },
        { input: 'guide_material',    label: 'image-name-guide' },
        { input: 'cert_bg_image',     label: 'image-name-bg' },
        { input: 'cert_sig_1_image',  label: 'image-name-sig1' },
        { input: 'cert_sig_2_image',  label: 'image-name-sig2' }
    ];

    fileInputs.forEach(f => {
        const input = document.getElementById(f.input);
        const label = document.getElementById(f.label);

        if(input && label){
            input.addEventListener('change', () => {
                label.textContent = input.files[0]?.name || "Ningún archivo seleccionado";
            });
        }
    });

})();
</script>
@endsection
