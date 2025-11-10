// resources/js/facturacion.js

// --- FUNCIÓN 1: INICIALIZAR MODAL DE "AGREGAR FACTURA" ---
function initializeModalFacturacion() {
    // Solo se ejecuta si el modal existe en la página
    const modal = document.getElementById('modalFactura');
    if (!modal) return; 

    const modalTitle = modal.querySelector('#modalTitle');
    const modalUserIdInput = modal.querySelector('#modal_user_id');
    const modalForm = modal.querySelector('#formFacturaModal');
    const spanClose = modal.querySelector(".close");
    const periodSelect = modal.querySelector("#modal_period_id");

    if (!modalTitle || !modalUserIdInput || !modalForm || !spanClose || !periodSelect) {
        console.error('Faltan elementos internos del modal (Title, UserID, Form, Close, Period Select).');
        return;
    }

    // Botones "Add Invoice" (dentro del acordeón)
    document.querySelectorAll('.add-invoice-btn').forEach(button => {
        // Evita duplicar el listener si ya existe
        if (button.listenerAttached) return; 
        button.listenerAttached = true;
        
        button.addEventListener('click', function() {
            // Lee los datos del botón que fue presionado
            openModal(this.dataset.userId, this.dataset.userName, this.dataset.periodId);
        });
    });
    
    // Lógica común para cerrar el modal (solo se añade una vez)
    if (!spanClose.listenerAttached) {
        spanClose.listenerAttached = true;
        spanClose.addEventListener('click', () => closeModal());
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && modal.style.display === 'flex') closeModal();
        });
    }
};

// --- FUNCIÓN 2: ABRIR EL MODAL (CON CORRECCIÓN DE CENTRADO Y PERÍODO) ---
function openModal(userId, userName, periodId) {
    const modal = document.getElementById('modalFactura');
    if (!modal) return;
    
    const modalTitle = modal.querySelector('#modalTitle');
    const modalUserIdInput = modal.querySelector('#modal_user_id');
    const modalForm = modal.querySelector('#formFacturaModal');
    const periodSelect = modal.querySelector('#modal_period_id');
    const firstFocusable = modal.querySelector('select, input, textarea, button');

    // Lee la URL del formulario (ya está puesta por Blade)
    const actionUrl = modalForm.action; 

    modalTitle.textContent = `Agregar Factura para ${userName || 'usuario seleccionado'}`;
    modalUserIdInput.value = userId || '';
    modalForm.reset(); 
    modalForm.action = actionUrl; 
    modalForm.querySelector('input[name="_method"]')?.remove(); 
    
    // Auto-seleccionar el período
    if (periodId) {
        periodSelect.value = periodId;
    }
    
    // Bloquea el scroll del body
    document.body.style.overflow = 'show';
    
    // --- CORRECCIÓN DE CENTRADO ---
    // Usa 'flex' para mostrarlo, activando el centrado del CSS
    modal.style.display = 'flex';

    setTimeout(() => {
        firstFocusable?.focus();
    }, 50); 
};

// --- FUNCIÓN 2B: CERRAR EL MODAL ---
function closeModal() {
    const modal = document.getElementById('modalFactura');
    if (modal) modal.style.display = 'none';
    // Restaura el scroll del body
    document.body.style.overflow = '';
};

// --- FUNCIÓN 3: INICIALIZAR DESPLIEGUE DE ABONOS ---
function initializePaymentToggle() {
    document.querySelectorAll('.icon-toggle').forEach(button => {
        if (button.listenerAttached) return; 
        button.listenerAttached = true;
        
        button.addEventListener('click', function() {
            const detailsRow = this.closest('tr').nextElementSibling;
            if (detailsRow && detailsRow.classList.contains('payment-details-row')) {
                detailsRow.classList.toggle('show');
            }
        });
    });
};

// --- FUNCIÓN 4: ENFOCAR ACORDEÓN DESPUÉS DE REDIRIGIR ---
function handleAnchorLink() {
    const hash = window.location.hash;
    
    if (hash && hash.startsWith('#user-anchor-')) {
        const element = document.getElementById(hash.substring(1)); 
        
        if (element && element.tagName === 'DETAILS') {
            
            element.open = true; 
            
            const parentPeriod = element.closest('.period-accordion > details');
            if (parentPeriod) {
                parentPeriod.open = true; 
            }

            setTimeout(() => {
                 element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300); 
        }
    }
};

// --- FUNCIÓN PRINCIPAL DE INICIALIZACIÓN ---
// (HEMOS QUITADO EL "export")
export function initializeFacturacionModal() {
    initializeModalFacturacion();
    initializePaymentToggle();
    handleAnchorLink();
}

// --- EJECUTOR AUTOMÁTICO ---
// Esto le dice al navegador: "Cuando todo el HTML esté cargado,
// ejecuta la función initFacturacionPage()"
document.addEventListener('DOMContentLoaded', function() {
    initFacturacionPage();
    console.log('¡Script de Facturación ejecutado!'); // <-- Añadí esto para confirmar
});