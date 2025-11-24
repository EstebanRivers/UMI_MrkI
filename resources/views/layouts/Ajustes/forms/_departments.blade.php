<div class="form-group">
    <label for="name">Nombre del Departamento</label>
    <input type="text" id="name" name="name" required
           value="{{ old('name', isset($item) ? $item->name : '') }}">
</div>

<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">