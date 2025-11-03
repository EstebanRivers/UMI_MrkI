<form id="createAcademicForm">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="user_id" id="academic_user_id">
    
    <div class="form-group">
        <label for="user_selector">Seleccionar Estudiante/Usuario</label>
        <select id="user_selector" class="form-control" required>
            <option value="">-- Seleccione un usuario --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->nombre }} {{ $user->apellido_paterno }} (ID: {{ $user->id }})</option>
            @endforeach
        </select>
    </div>

    <hr>
    
    <div id="userDataDisplay" style="display: none; border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
        <h5>Datos del Usuario Seleccionado:</h5>
        <div class="form-group">
            <label>Nombre Completo:</label>
            <input type="text" id="user_full_name" class="form-control" readonly>
        </div>
    </div>
    
    <div id="academicFields" style="display: none;">
        <div class="form-group">
            <label for="carrera_selector">Carrera</label>
            <select id="carrera_selector" name="carrera" class="form-control" required>
                @foreach($carreras as $carrera)
                    <option value="{{ $carrera }}">{{ $carrera }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="status_selector">Status</label>
            <select id="status_selector" name="status" class="form-control" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="modal-footer-custom" style="margin-top: 20px;">
        <button type="submit" id="saveAcademicBtn" class="btn btn-primary" style="display: none;">Guardar Datos Académicos</button>
        <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancelar</button>
    </div>
</form>

<div id="formMessages" style="margin-top: 15px;"></div>