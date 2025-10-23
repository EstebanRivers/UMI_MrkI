<div class="form-group">
    <label for="name">Nombre del Departamento</label>
    <input type="text" id="name" name="name" required
           value="{{ old('name', $item->name ?? '') }}">
</div>

<div class="form-group">
    <label for="institution_id">Unidad de Negocio</label>
    <select id="institution_id" name="institution_id" required>
        <option value="">Seleccione una unidad</option>
        
        {{-- Esto se rellena desde AjustesController@getFormData --}}
        @foreach($institutions as $institution)
            <option value="{{ $institution->id }}" 
                    {{ (isset($item) && $item->institution_id == $institution->id) ? 'selected' : '' }}>
                {{ $institution->name }}
            </option>
        @endforeach
    </select>
</div>