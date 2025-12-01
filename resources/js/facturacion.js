/* 
// ==========================================================================
// 1. HANDLERS GLOBALES (Definidos en window para poder limpiar y reiniciar)
// ==========================================================================

// Handler principal de Clics (Delegación de Eventos)
window.handleFacturacionClick = function(e) {
    
    // --- CASO A: BOTONES "VER ABONOS" (.icon-toggle) ---
    const toggleBtn = e.target.closest('.icon-toggle');
    if (toggleBtn) {
        const row = toggleBtn.closest('tr');
        const detailsRow = row.nextElementSibling;
        
        if (detailsRow && detailsRow.classList.contains('payment-details-row')) {
            // Toggle manual compatible con Blade (display: none)
            if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                detailsRow.style.display = 'table-row';
            } else {
                detailsRow.style.display = 'none';
            }
        }
        return; // Detenemos la ejecución aquí
    }

    // --- CASO B: BOTONES "AGREGAR FACTURA" (.add-invoice-btn) ---
    // Esto maneja tanto el botón general del usuario como el botón pequeño del mes
    const addBtn = e.target.closest('.add-invoice-btn');
    if (addBtn) {
        e.preventDefault();
        
        // Obtenemos todos los datos del botón
        openModal(
            addBtn.dataset.userId, 
            addBtn.dataset.userName, 
            addBtn.dataset.periodId,
            addBtn.dataset.date,  // Opcional (para botones de mes específico)
            addBtn.dataset.label  // Opcional (para prellenar concepto)
        );
        return;
    }
    
    // --- CASO C: CERRAR MODAL (Click en fondo oscuro o botón X) ---
    const modal = document.getElementById('modalFactura');
    if (modal && modal.style.display === 'flex') {
        // Si click en el fondo (overlay) O en el botón .close
        if (e.target === modal || e.target.closest('.close')) {
            closeModal();
        }
    }
};

// Handler para tecla ESC (Cerrar modal)
window.handleFacturacionEsc = function(ev) {
    const modal = document.getElementById('modalFactura');
    if (modal && ev.key === 'Escape' && modal.style.display === 'flex') closeModal();
};


// ==========================================================================
// 2. FUNCIÓN DE INICIALIZACIÓN SEGURA (Limpieza + Asignación)
// ==========================================================================
// Variable global para controlar si ya se inició
window.facturacionInitialized = false; 

function initializeGlobalListeners() {
    // 1. SI YA EXISTE, NO HACEMOS NADA (EVITA EL TRIPLE LISTENER)
    if (window.facturacionInitialized) {
        console.log('🛑 Facturación: Listeners ya estaban activos (evitando duplicados).');
        return; 
    }

    // 2. Limpieza preventiva (por si acaso)
    document.removeEventListener('click', window.handleFacturacionClick);
    document.removeEventListener('keydown', window.handleFacturacionEsc);
    
    // 3. Asignación de Listeners
    document.addEventListener('click', window.handleFacturacionClick);
    document.addEventListener('keydown', window.handleFacturacionEsc);

    // 4. MARCAMOS COMO INICIADO
    window.facturacionInitialized = true;

    console.log('✅ Facturación: Listeners activos y listos (Primera vez).');
}


// ==========================================================================
// 3. LÓGICA DEL MODAL (Abrir, Llenar datos, Cerrar)
// ==========================================================================


function openModal(userId, userName, periodId, date = null, label = null) {
    const modal = document.getElementById('modalFactura');
if (!modal) {
    console.error('❌ ERROR: El HTML del modal (#modalFactura) NO existe en el DOM.');
    return;
}
    // Referencias
    const modalTitle = modal.querySelector('#modalTitle');
    const modalUserIdInput = modal.querySelector('#modal_user_id');
    const modalForm = modal.querySelector('#formFacturaModal');
    const periodSelect = modal.querySelector('#modal_period_id');
    
    // Referencias para la fecha
    const dateInput = modal.querySelector('#modal_fecha'); 
    const dateText = modal.querySelector('#texto_fecha_vencimiento');
    const conceptoInput = modal.querySelector('#modal_concepto');

    // ... (Lógica de limpieza del form que ya tenías) ...
    if (!modalForm.dataset.originalAction) {
        modalForm.dataset.originalAction = modalForm.action;
    }
    modalForm.reset();
    modalForm.action = modalForm.dataset.originalAction;
    modalForm.querySelector('input[name="_method"]')?.remove(); 

    // --- LLENADO DE DATOS ---
    modalTitle.textContent = `Agregar Factura a: ${userName || 'Usuario'}`;
    modalUserIdInput.value = userId || '';

    // REQUISITO 2: El periodo ya está filtrado en el HTML por Blade (solo el activo),
    // pero por seguridad visual nos aseguramos que coincida si el botón traía un ID.
    if (periodId && periodSelect) {
        periodSelect.value = periodId; 
    }

    // REQUISITO 1: Asignar fecha automáticamente
    if (date && dateInput) {
        // 1. Asignar al input oculto (para enviar a BD)
        dateInput.value = date; 
        
        // 2. Mostrar visualmente al usuario
        if(dateText) {
            // Convertir fecha YYYY-MM-DD a formato legible DD/MM/YYYY
            const [year, month, day] = date.split('-');
            dateText.textContent = `${day}/${month}/${year}`;
            dateText.style.color = '#223F70'; // Azul institucional
        }
    } else {
        // Fallback por si no llega fecha (ej. botón general)
        // Aquí podrías poner la fecha de hoy o dejarlo vacío
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
        if(dateText) dateText.textContent = "Fecha actual (por defecto)";
    }

    // Pre-llenar concepto si existe etiqueta
    if (label && conceptoInput) {
        conceptoInput.value = `Colegiatura ${label}`;
    }

    // Mostrar modal (quitando el bloqueo de scroll que corregimos antes)
    document.body.style.overflow = 'hidden';
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('modalFactura');
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}


// ==========================================================================
// 4. LÓGICA DE ANCLAJE (Scroll automático al volver de guardar)
// ==========================================================================
function handleAnchorLink() {
    const hash = window.location.hash;
    // Si la URL tiene #user-anchor-123...
    if (hash && hash.startsWith('#user-anchor-')) {
        const element = document.getElementById(hash.substring(1));
        
        // Abrimos los acordeones necesarios
        if (element && element.tagName === 'DETAILS') {
            element.open = true; // Abre el usuario
            const parent = element.closest('.period-accordion > details');
            if (parent) parent.open = true; // Abre el periodo
            
            // Scroll suave hacia el elemento
            setTimeout(() => {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    }
}


// ==========================================================================
// 5. EXPORT Y AUTO-EJECUCIÓN (Para compatibilidad total)
// ==========================================================================

// Exportamos para que app.js no de error de sintaxis
export function initializeFacturacionModal() {
    initializeGlobalListeners();
    handleAnchorLink();
}

// Asignación Global
window.initFacturacionPage = initializeFacturacionModal;

// Función "Check y Ejecuta": Solo corre si estamos en la vista de facturación
const runIfOnPage = () => {
    if (document.querySelector('.filter-controls') || document.getElementById('modalFactura')) {
        window.initFacturacionPage();
    }
};

// --- TRIGGERS DE EJECUCIÓN (Cubre F5, Navegación lenta y rápida) ---

// 1. Ejecutar ya (por si el import llega tarde)
runIfOnPage();

// 2. Ejecutar cuando el DOM esté listo (F5 estándar)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runIfOnPage);
}

// 3. Ejecutar cuando todo haya cargado (Imágenes/Recursos - F5 Seguro)
window.addEventListener('load', runIfOnPage);*/