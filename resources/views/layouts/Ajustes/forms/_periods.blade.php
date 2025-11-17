
<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

<div class="form-group">
    <label for="start_date">Mes y Año de Inicio</label>
    <input type="month" id="start_date" name="start_date" required
           value="{{ old('start_date', isset($item) ? optional($item->start_date)->format('Y-m') : '') }}">
</div>

<div class="form-group">
    <label for="end_date">Mes y Año de Fin</label>
    <input type="month" id="end_date" name="end_date" required
           value="{{ old('end_date', isset($item) ? optional($item->end_date)->format('Y-m') : '') }}">
</div>



<div class="form-group">
    <label for="monthly_payments_count">Número de Mensualidades</label>
    <input type="number" id="monthly_payments_count" name="monthly_payments_count"
           min="1" step="1"
           value="{{ old('monthly_payments_count', $item->monthly_payments_count ?? '') }}">
</div>

