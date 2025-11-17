<div class="form-group">
    <label for="department_id">Departamento</label>
    <select id="department_id" name="department_id" required>
        <option value="">Seleccione un departamento</option>
        
        @foreach($departments as $department)
            <option value="{{ $department->id }}"
                    {{ (isset($item) && $item->department_id == $department->id) ? 'selected' : '' }}>
                {{ $department->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="name">Nombre del Puesto</label>
    <input type="text" id="name" name="name" required
           value="{{ old('name', isset($item) ? $item->name : '') }}">
</div>