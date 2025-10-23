{{-- resources/views/layouts/Ajustes/forms/_period.blade.php --}}

<div class="form-group">
    <label for="name">Nombre del Periodo (Ej. CICLO 2025-01)</label>
    <input type="text" id="name" name="name" required
           value="{{ old('name', $item->name ?? '') }}">
</div>

<div class="form-group">
    <label for="start_date">Fecha de Inicio</label>
    <input type="date" id="start_date" name="start_date" required
           value="{{ old('start_date', isset($item) ? optional($item->start_date)->format('Y-m-d') : '') }}">
</div>

<div class="form-group">
    <label for="end_date">Fecha de Fin</label>
    <input type="date" id="end_date" name="end_date" required
           value="{{ old('end_date', isset($item) ? optional($item->end_date)->format('Y-m-d') : '') }}">
</div>

{{-- 
    El campo 'is_active' se manejará automáticamente desde el controlador.
    (Al crear uno nuevo, pondrá este como 'activo' y los demás como 'inactivos').
--}}