<input type="hidden" name="institution_id" value="{{ session('active_institution_id') }}">

{{-- CAMPO OCULTO: Pasa los datos guardados de PHP a JS --}}
<textarea id="payment_dates_data" style="display:none;">
    @if(isset($item) && $item->payment_dates)
        {{ json_encode($item->payment_dates) }}
    @else
        []
    @endif
</textarea>

<div class="form-group">
    <label for="start_date">Mes y Año de Inicio</label>
    <input type="month" id="start_date" name="start_date" required class="form-control"
           value="{{ old('start_date', isset($item) ? optional($item->start_date)->format('Y-m') : '') }}">
</div>

<div class="form-group">
    <label for="end_date">Mes y Año de Fin</label>
    <input type="month" id="end_date" name="end_date" required class="form-control"
           value="{{ old('end_date', isset($item) ? optional($item->end_date)->format('Y-m') : '') }}">
</div>

<div class="form-group">
    <label for="monthly_payments_count">Número de Mensualidades (Automático)</label>
    <input type="number" id="monthly_payments_count" name="monthly_payments_count"
           readonly class="form-control" style="background-color: #e9ecef; font-weight:bold;"
           value="{{ old('monthly_payments_count', $item->monthly_payments_count ?? 0) }}">
</div>

{{-- CONTENEDOR DE FECHAS DINÁMICAS --}}
<div class="form-group" style="border-top: 2px solid #eee; margin-top: 20px; padding-top: 15px;">
    <label style="color: #223F70; font-size: 1.1em; margin-bottom: 15px; display: block;">
        <img src="{{ asset('images/icons/calendar-solid-full.svg') }}" style="width: 16px; vertical-align: middle;" onerror="this.style.display='none'"> 
        Configuración de Vencimientos por Mes:
    </label>
    
    <div id="dates-list-container" style="display: flex; flex-direction: column; gap: 10px;">
        <p id="no-dates-msg" style="color: #777; font-style: italic;">Selecciona fechas de inicio y fin para configurar los pagos.</p>
    </div>
</div>

<script>
(function() {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const countInput = document.getElementById('monthly_payments_count');
    const container = document.getElementById('dates-list-container');
    const noDatesMsg = document.getElementById('no-dates-msg');
    
    // Leer datos guardados (Modo Edición)
    let savedDates = [];
    try {
        const rawData = document.getElementById('payment_dates_data').value.trim();
        if(rawData) savedDates = JSON.parse(rawData);
    } catch(e) { console.error("Error parseando fechas", e); }

    function renderDates() {
        const startVal = startInput.value; 
        const endVal = endInput.value;     

        if (!startVal || !endVal) {
            container.innerHTML = '';
            if(noDatesMsg) container.appendChild(noDatesMsg);
            countInput.value = 0;
            return;
        }

        // Usamos T12:00:00 para evitar problemas de zona horaria
        const startDate = new Date(startVal + "-01T12:00:00");
        const endDate = new Date(endVal + "-01T12:00:00");

        // Calcular meses
        let months = (endDate.getFullYear() - startDate.getFullYear()) * 12;
        months -= startDate.getMonth();
        months += endDate.getMonth();
        const totalMonths = months + 1; 

        if (totalMonths <= 0) {
            countInput.value = 0;
            container.innerHTML = '<div style="color: red;">La fecha fin debe ser mayor a la inicial.</div>';
            return;
        }

        countInput.value = totalMonths;
        container.innerHTML = ''; 

        for (let i = 0; i < totalMonths; i++) {
            // Calcular mes actual del ciclo
            let currentMonth = new Date(startDate);
            currentMonth.setMonth(startDate.getMonth() + i);
            
            const monthLabel = currentMonth.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
            const formattedLabel = monthLabel.charAt(0).toUpperCase() + monthLabel.slice(1);

            // Calcular valor del input
            let valueToUse = "";
            
            // A. Si existe un valor guardado en esta posición (Edición), úsalo
            if (savedDates[i]) {
                valueToUse = savedDates[i];
            } 
            // B. Si es nuevo, calcula día 10 por defecto
            else {
                const y = currentMonth.getFullYear();
                const m = String(currentMonth.getMonth() + 1).padStart(2, '0');
                valueToUse = `${y}-${m}-10`; 
            }

            const row = document.createElement('div');
            row.style.cssText = `display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 10px; border-radius: 8px; border: 1px solid #e9ecef;`;

            row.innerHTML = `
                <div style="font-weight: 600; color: #333; width: 40%;">${formattedLabel}</div>
                <div style="width: 55%;">
                    <label style="font-size: 0.8em; color: #666; display:block;">Vence:</label>
                    <input type="date" name="payment_dates[]" value="${valueToUse}" required class="form-control" style="width: 100%;">
                </div>
            `;
            container.appendChild(row);
        }
    }

    // Listeners
    startInput.addEventListener('change', () => { 
        savedDates = []; // Resetear si cambia inicio para recalcular lógica
        renderDates(); 
    });
    endInput.addEventListener('change', renderDates);

    // Ejecución inicial
    if (startInput.value && endInput.value) {
        renderDates();
    }
})();
</script>