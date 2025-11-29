<div class="form-group">
    <label for="name">Nombre de Unidad</label>
    <input type="text" id="name" name="name" required
           value="{{ old('name', isset($item) ? $item->name : '') }}">
         
</div>
<div class="form-group">
    <label for="logo_path">Logo</label>
    <input type="file" id="logo_path" name="logo_path" accept="image/*">
   
   
    @if(isset($item) && $item->logo_path)
        <div style="margin-top: 10px;">
            <img src="{{ asset('storage/' . $item->logo_path) }}" alt="Logo actual" style="max-width: 100px; max-height: 50px; border-radius: 4px;">
            <small style="display: block; color: #555;">Logo actual. Selecciona un archivo para reemplazarlo.</small>
        </div>
    @endif
</div>