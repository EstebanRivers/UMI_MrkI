{{-- DATOS PERSONALES --}}
<div class="form-group">
    <label for="nombre">Nombre(s)</label>
    <input type="text" id="nombre" name="nombre" required
           value="{{ old('nombre', $item->nombre ?? '') }}">
</div>
<div class="form-group">
    <label for="apellido_paterno">Apellido Paterno</label>
    <input type="text" id="apellido_paterno" name="apellido_paterno" required
           value="{{ old('apellido_paterno', $item->apellido_paterno ?? '') }}">
</div>
<div class="form-group">
    <label for="apellido_materno">Apellido Materno</label>
    <input type="text" id="apellido_materno" name="apellido_materno"
           value="{{ old('apellido_materno', $item->apellido_materno ?? '') }}">
</div>
<div class="form-group">
    <label for="RFC">Usuario (RFC)</label>
    <input type="text" id="RFC" name="RFC" required maxlength="13"
           value="{{ old('RFC', $item->RFC ?? '') }}" style="text-transform: uppercase;">
</div>
<hr style="margin: 15px 0;">

{{-- DATOS DE ACCESO --}}
<div class="form-group">
    <label for="email">Correo Electrónico</label>
    <input type="email" id="email" name="email" required
           value="{{ old('email', $item->email ?? '') }}">
</div>
<div class="form-group">
    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" {{ isset($item) ? '' : 'required' }}>
    @if(isset($item))
        <small style="display: block; color: #555;">Dejar en blanco para no cambiar la contraseña.</small>
    @endif
</div>
<div class="form-group">
    <label for="password_confirmation">Confirmar Contraseña</label>
    <input type="password" id="password_confirmation" name="password_confirmation" {{ isset($item) ? '' : 'required' }}>
</div>
<hr style="margin: 15px 0;">

{{-- ASIGNACIÓN, ROLES Y CAMPOS CONDICIONALES --}}
<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

<div class="form-group">
    <label for="role_id">Rol Principal</label>
    <select id="role_id_select" name="role_id" required>
        <option value="">-- Seleccione Rol --</option>
        {{-- JS llenará esto --}}
    </select>
</div>

{{-- Wrapper de Módulos (con lógica de 'checked' para editar) --}}
<div id="admin-modules-wrapper" class="form-group" style="display: none; border: 1px solid #eee; padding: 10px; border-radius: 4px; background-color: #f9f9f9;">
    <label>Módulos a Habilitar para Control Administrativo:</label>
    @php
        $modules = old('modules_enabled', $enabled_modules ?? []);
    @endphp
    <div class="checkbox-line">
        <input type="checkbox" id="module_control_academico" name="modules_enabled[]" value="control_academico"
               {{ in_array('control_academico', $modules) ? 'checked' : '' }}>
        <label for="module_control_academico">Control Académico</label>
    </div>
    <div class="checkbox-line">
        <input type="checkbox" id="module_planeacion_vinculacion" name="modules_enabled[]" value="planeacion_vinculacion"
               {{ in_array('planeacion_vinculacion', $modules) ? 'checked' : '' }}>
        <label for="module_planeacion_vinculacion">Planeación y Vinculación</label>
    </div>
    <div class="checkbox-line">
        <input type="checkbox" id="module_control_escolar" name="modules_enabled[]" value="control_escolar"
               {{ in_array('control_escolar', $modules) ? 'checked' : '' }}>
        <label for="module_control_escolar">Control Escolar</label>
    </div>
</div>

{{-- Ocultar campos con Blade si es Universidad --}}
@php
    $isUniversity = $isActiveInstitutionUniversity ?? false;
@endphp

{{-- El wrapper se oculta con style si $isUniversity es true --}}
<div id="department-field-wrapper" class="form-group" 
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="department_id">Departamento (Opcional)</label>
    <select id="department_id" name="department_id">
        <option value="">N/A</option>
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                    {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                {{ $department->name }}
            </option>
        @endforeach
    </select>
</div>

{{-- El wrapper se oculta con style si $isUniversity es true --}}
<div id="workstation-field-wrapper" class="form-group" 
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="workstation_id">Puesto (Opcional)</label>
    
    
    <select id="workstation_id" name="workstation_id">
        <option value="">N/A</option>
        
        {{-- !! HEMOS ELIMINADO EL @foreach !! --}}
        {{-- JavaScript se encargará de llenar esto --}}
        
    </select>
</div>

{{-- El script que hicimos funcionar (con los console.log de depuración) --}}
<script>
setTimeout(function() {
    // --- Variables ---
    const roleSelect = document.getElementById('role_id_select');
    const adminModulesWrapper = document.getElementById('admin-modules-wrapper');
    
    // !! NUEVAS VARIABLES !!
    const departmentSelect = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');

    // Verificación de elementos
    if (!roleSelect || !adminModulesWrapper || !departmentSelect || !workstationSelect) {
        console.error("Error inicializando script: Faltan elementos (roleSelect, adminModulesWrapper, departmentSelect, o workstationSelect).");
        return;
    }

    // --- Variables de Blade ---
    const allRoles = @json($all_roles ?? []);
    const currentRoleId = @json(old('role_id', $item->role_id ?? null));
    const universityName = @json($universityName); 
    const adminRoleName = @json($adminRoleName);   
    const activeInstitutionName = @json($activeInstitutionName);

    // !! NUEVAS VARIABLES DE BLADE !!
    // (Pasadas desde getFormData)
    const allWorkstations = @json($workstations ?? []); 
    // (Para saber cuál seleccionar al editar)
    const currentWorkstationId = @json(old('workstation_id', $item->workstation_id ?? null));

    // !! DEBUG (se mantiene igual) !!
    console.log('--- DEBUG DATOS DE BLADE ---');
    console.log('Todos los Roles:', allRoles);
    console.log('Todos los Puestos:', allWorkstations);
    // ... (etc.)


    if (!Array.isArray(allRoles) || !universityName || !adminRoleName || !activeInstitutionName || !Array.isArray(allWorkstations)) {
        console.error('Error: Faltan variables clave de Blade (roles, nombres, o allWorkstations).');
        return;
    }

    // ... (La función getSelectedRoleName() no cambia) ...
    function getSelectedRoleName() {
        const selectedRoleId = roleSelect.value;
        if (!selectedRoleId) return null;
        const selectedRole = allRoles.find(role => role.id == selectedRoleId);
        return selectedRole ? selectedRole.name : null; 
    }

    // ... (La función updateModuleVisibility() no cambia) ...
    function updateModuleVisibility() {
        const selectedRoleName = getSelectedRoleName(); 
        if (activeInstitutionName === universityName && selectedRoleName === adminRoleName) {
            adminModulesWrapper.style.display = 'block';
        } else {
            adminModulesWrapper.style.display = 'none';
        }
    }
    
    // ... (La función updateRolesDropdown() no cambia) ...
    function updateRolesDropdown() {
        let filteredRoles = [];
        roleSelect.innerHTML = '<option value="">-- Seleccione Rol --</option>';

        if (activeInstitutionName === universityName) {
            console.log("Filtro: Universidad");
            const uniRoles = ['estudiante', 'docente', 'control_administrativo', 'control_escolar'];
            filteredRoles = allRoles.filter(role => uniRoles.includes(role.name));
        } else {
            console.log("Filtro: Corporativo");
            const corpRoles = ['anfitrion', 'master', 'gerente_capacitacion', 'gerente_th'];
            filteredRoles = allRoles.filter(role => corpRoles.includes(role.name));
        }

        console.log('Roles Filtrados:', filteredRoles);
        filteredRoles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.display_name;
            if (currentRoleId && role.id == currentRoleId) {
                option.selected = true;
            }
            roleSelect.appendChild(option);
        });
        updateModuleVisibility();
    }


    // !! ============================================== !!
    // !! NUEVA FUNCIÓN PARA FILTRAR PUESTOS !!
    // !! ============================================== !!
    function updateWorkstationDropdown() {
        const selectedDepartmentId = departmentSelect.value;
        
        // Limpiar opciones anteriores
        workstationSelect.innerHTML = '<option value="">N/A</option>';

        // Si se seleccionó un departamento válido
        if (selectedDepartmentId) {
            // 1. Filtrar los puestos
            const filteredWorkstations = allWorkstations.filter(workstation => {
                // Comparamos el ID del depto del puesto con el ID del depto seleccionado
                return workstation.department_id == selectedDepartmentId;
            });

            console.log('Puestos filtrados:', filteredWorkstations);

            // 2. Llenar el dropdown con los resultados
            filteredWorkstations.forEach(workstation => {
                const option = document.createElement('option');
                option.value = workstation.id;
                option.textContent = workstation.name;
                
                // 3. Si es el puesto actual (al editar), seleccionarlo
                if (currentWorkstationId && workstation.id == currentWorkstationId) {
                    option.selected = true;
                }
                workstationSelect.appendChild(option);
            });
        }
    }
    // ==============================================

    // --- Event Listeners ---
    roleSelect.addEventListener('change', updateModuleVisibility);
    
    // !! NUEVO EVENT LISTENER !!
    // Cuando cambie el departamento, actualiza los puestos
    departmentSelect.addEventListener('change', updateWorkstationDropdown);


    // --- Ejecución Inicial ---
    updateRolesDropdown(); // Llena los roles al cargar
    
    // !! NUEVA EJECUCIÓN INICIAL !!
    // Llena los puestos al cargar (importante para "Editar")
    updateWorkstationDropdown(); 

}, 0);
</script>