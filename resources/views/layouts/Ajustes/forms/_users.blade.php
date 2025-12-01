
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
           class="form-control @error('RFC') is-invalid @enderror" 
           value="{{ old('RFC', $item->RFC ?? '') }}" 
           style="text-transform: uppercase;">
    
    {{-- !! ESTO MUESTRA EL ERROR !! --}}
    @error('RFC')
        <span class="invalid-feedback" role="alert" style="color: #dc3545; font-size: 0.85em; display: block; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
<hr style="margin: 15px 0;">


<div class="form-group">
    <label for="email">Correo Electrónico</label>
    <input type="email" id="email" name="email" required
           class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $item->email ?? '') }}">
           
   
    @error('email')
        <span class="error-message" style="color: red; font-size: 0.85em; display: block; margin-top: 5px;">
            {{ $message }}
        </span>
    @enderror
</div>

{{-- Campo: Contraseña --}}
<div class="form-group">
    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" 
           class="form-control @error('password') is-invalid @enderror"
           {{ isset($item) ? '' : 'required' }}>
    
    {{-- Aquí se mostrará el mensaje "Las contraseñas no coinciden" --}}
    @error('password')
        <span class="invalid-feedback" role="alert" style="color: #dc3545; font-size: 0.85em; display: block; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
    
    @if(isset($item))
        <small style="display: block; color: #555;">Dejar en blanco para no cambiar la contraseña.</small>
    @endif
</div>

{{-- Campo: Confirmar Contraseña --}}
<div class="form-group">
    <label for="password_confirmation">Confirmar Contraseña</label>
    {{-- ¡OJO! El name DEBE ser 'password_confirmation' --}}
    <input type="password" id="password_confirmation" name="password_confirmation" 
           class="form-control"
           {{ isset($item) ? '' : 'required' }}>
</div>
<hr style="margin: 15px 0;">


<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

<div class="form-group">
    <label for="role_id">Rol Principal</label>
    <select id="role_id_select" name="role_id" required>
        <option value="">-- Seleccione Rol --</option>
       
    </select>
</div>

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

    @error('modules_enabled')
        <span class="invalid-feedback" style="display: block; color: #dc3545; font-size: 0.85em; margin-top: 5px;">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>

@php
    $isUniversity = $isActiveInstitutionUniversity ?? false;
@endphp


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


<div id="workstation-field-wrapper" class="form-group" 
     style="{{ $isUniversity ? 'display: none;' : '' }}">
    <label for="workstation_id">Puesto (Opcional)</label>
    
    
    <select id="workstation_id" name="workstation_id">
        <option value="">N/A</option>
        
     
        
    </select>
</div>


<script>
setTimeout(function() {
    
    const roleSelect = document.getElementById('role_id_select');
    const adminModulesWrapper = document.getElementById('admin-modules-wrapper');
    
   
    const departmentSelect = document.getElementById('department_id');
    const workstationSelect = document.getElementById('workstation_id');

    
    if (!roleSelect || !adminModulesWrapper || !departmentSelect || !workstationSelect) {
        console.error("Error inicializando script: Faltan elementos (roleSelect, adminModulesWrapper, departmentSelect, o workstationSelect).");
        return;
    }

  
    const allRoles = @json($all_roles ?? []);
    const currentRoleId = @json(old('role_id', $item->role_id ?? null));
    const universityName = @json($universityName); 
    const adminRoleName = @json($adminRoleName);   
    const activeInstitutionName = @json($activeInstitutionName);

    
    
    const allWorkstations = @json($workstations ?? []); 
    
    const currentWorkstationId = @json(old('workstation_id', $item->workstation_id ?? null));

    
    console.log('--- DEBUG DATOS DE BLADE ---');
    console.log('Todos los Roles:', allRoles);
    console.log('Todos los Puestos:', allWorkstations);
   


    if (!Array.isArray(allRoles) || !universityName || !adminRoleName || !activeInstitutionName || !Array.isArray(allWorkstations)) {
        console.error('Error: Faltan variables clave de Blade (roles, nombres, o allWorkstations).');
        return;
    }

   
    function getSelectedRoleName() {
        const selectedRoleId = roleSelect.value;
        if (!selectedRoleId) return null;
        const selectedRole = allRoles.find(role => role.id == selectedRoleId);
        return selectedRole ? selectedRole.name : null; 
    }

   
    function updateModuleVisibility() {
        const selectedRoleName = getSelectedRoleName(); 
        if (activeInstitutionName === universityName && selectedRoleName === adminRoleName) {
            adminModulesWrapper.style.display = 'block';
        } else {
            adminModulesWrapper.style.display = 'none';
        }
    }
    
  
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


    function updateWorkstationDropdown() {
        const selectedDepartmentId = departmentSelect.value;
        
        
        workstationSelect.innerHTML = '<option value="">N/A</option>';

      
        if (selectedDepartmentId) {
            
            const filteredWorkstations = allWorkstations.filter(workstation => {
                
                return workstation.department_id == selectedDepartmentId;
            });

            console.log('Puestos filtrados:', filteredWorkstations);

            
            filteredWorkstations.forEach(workstation => {
                const option = document.createElement('option');
                option.value = workstation.id;
                option.textContent = workstation.name;
                
               
                if (currentWorkstationId && workstation.id == currentWorkstationId) {
                    option.selected = true;
                }
                workstationSelect.appendChild(option);
            });
        }
    }
    

    
    roleSelect.addEventListener('change', updateModuleVisibility);
    
    
    departmentSelect.addEventListener('change', updateWorkstationDropdown);


   
    updateRolesDropdown(); 
    
    
    updateWorkstationDropdown(); 

}, 0);
</script>