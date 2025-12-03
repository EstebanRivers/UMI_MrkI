<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="description" content="Sistema de gestión UHTA">
  <meta name="theme-color" content="#e69a37">
  <meta name="robots" content="noindex, nofollow">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <title>@yield('title','Dashboard')</title>
  
  {{-- Vite inyecta los enlaces a CSS/JS de resources --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
  {{-- Botón menú móvil --}}
  <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Abrir menú">
    <svg viewBox="0 0 24 24" fill="currentColor">
      <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
    </svg>
  </button>

  {{-- Contenedor principal: sidebar + contenido --}}
  <div class="app-container">
    {{-- Sidebar --}}
    @include('layouts.components.sidebar')
    
    <main class="main-content" id="main-content">
       <div class="header">
          <div class="header-user-info">
             <span class="user-name ">{{ Auth::user()->nombre }} </span>
              <div class="user-context">
                  <span class="user-role" style="margin-left: 0.3rem;">
                      {{ session('active_role_display_name', ', Sin rol') }}
                  </span>
                  <span class="user-institution">en {{ session('active_institution_name', 'Sin institución') }}</span>
              </div>
          </div>
          <div class="context-switcher">
            @if (count($availableContexts) > 1)
                <button id="context-switcher-button" class="context-switcher-button">
                    <img src="{{ asset('images/icons/gear-solid-full.svg') }}" alt="Ajustes">
                </button>

                <div id="context-switcher-menu" class="context-switcher-menu">
                    <div class="context-switcher-header">Unidad de Negocio</div>
                    <ul>
                        @foreach ($availableContexts as $context)
                            <li>
                                <a href="{{ route('context.switch', ['institutionId' => $context['institution_id'], 'roleId' => $context['role_id']]) }}"
                                  data-no-spa>
                                    <span class="institution">{{ $context['institution_name'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
          </div>
        </div>
    
      {{-- Contenido específico de cada página --}}
      @yield('content')
    </main>
  </div>
{{-- ======================= SCRIPT MAESTRO ======================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ============================================================
       1. ARRANQUE INICIAL
    ============================================================ */
    executeScrollLogic();
    checkAndShowAlerts();
    
    // Reintento por si la carga es lenta
    setTimeout(() => {
        executeScrollLogic();
    }, 500);

    /* ============================================================
       2. OBSERVADOR SPA (Detectar navegación sin recarga)
    ============================================================ */
    const mainContent = document.getElementById('main-content');
    if (mainContent) {
        const observer = new MutationObserver(() => {
            setTimeout(() => {
                executeScrollLogic();
                checkAndShowAlerts();
            }, 300);
        });
        observer.observe(mainContent, { childList: true, subtree: true });
    }

    /* ============================================================
       3. VALIDACIÓN FORMULARIO (Modal Factura)
    ============================================================ */
    const formFactura = document.getElementById('formFacturaModal');
    if (formFactura) {
        formFactura.addEventListener('submit', function(e) {
            let valid = true;
            let errorMessage = '';

            // PDF (Opcional)
            const pdfInput = document.getElementById('modal_archivo_pdf');
            if (pdfInput && pdfInput.files.length > 0 && !pdfInput.files[0].name.toLowerCase().endsWith('.pdf')) {
                valid = false;
                errorMessage = 'El archivo debe ser formato PDF (.pdf)';
            }

            // XML (Opcional)
            if (valid) {
                const xmlInput = document.getElementById('modal_archivo_xml');
                if (xmlInput && xmlInput.files.length > 0 && !xmlInput.files[0].name.toLowerCase().endsWith('.xml')) {
                    valid = false;
                    errorMessage = 'El archivo debe ser formato XML (.xml)';
                }
            }

            // Monto
            if (valid) {
                const montoEl = document.getElementById('modal_monto');
                if (montoEl) {
                    const montoVal = montoEl.value;
                    if (!montoVal || parseFloat(montoVal) <= 0) {
                        valid = false;
                        errorMessage = 'El monto no es válido. Selecciona un concepto nuevamente.';
                    }
                }
            }

            if (!valid) {
                e.preventDefault();
                Swal.fire({ icon: 'error', title: 'Datos Incorrectos', text: errorMessage, confirmButtonColor: '#d33' });
            }
        });
    }

    /* ============================================================
       4. PRECIOS AUTOMÁTICOS
    ============================================================ */
    document.addEventListener('change', (e) => {
        if (e.target.id === 'modal_concepto') {
            const select = e.target;
            const inputReal = document.getElementById('modal_monto');
            const inputVisible = document.getElementById('modal_monto_visible');
            
            if (select && inputReal && inputVisible) {
                const option = select.options[select.selectedIndex];
                const precio = option.getAttribute('data-amount');

                if (precio) {
                    inputReal.value = precio;
                    inputVisible.value = parseFloat(precio).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });

                    // Flash visual
                    inputVisible.style.transition = "background-color 0.3s";
                    inputVisible.style.backgroundColor = "#ffffff";
                    inputVisible.style.borderColor = "#999";
                    setTimeout(() => {
                        inputVisible.style.backgroundColor = "#f8f9fa";
                        inputVisible.style.borderColor = "#ccc";
                    }, 400);
                } else {
                    inputReal.value = '';
                    inputVisible.value = '';
                }
            }
        }
    });

    /* ============================================================
       5. ABRIR/CERRAR MODAL
    ============================================================ */
    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('.js-trigger-factura');
        if (addBtn) {
            e.preventDefault();
            let modal = document.getElementById('modalFactura');
            const zombie = document.querySelector('body > #modalFactura');

            if (modal) {
                if (zombie && zombie !== modal) zombie.remove();
                if (modal.parentNode !== document.body) document.body.appendChild(modal);
            } else if (zombie) {
                modal = zombie;
            }

            if (modal) fillAndOpenModal(modal, addBtn);
            return;
        }

        if (e.target.closest('.close') || e.target.classList.contains('modal')) {
            const modal = document.getElementById('modalFactura');
            if (modal) closeFacturaModal(modal);
        }
        
        // Toggle Detalles (Abonos)
        const toggleBtn = e.target.closest('.icon-toggle');
        if (toggleBtn) {
            const row = toggleBtn.closest('tr');
            const detailsRow = row.nextElementSibling;
            if (detailsRow && detailsRow.classList.contains('payment-details-row')) {
                const isHidden = window.getComputedStyle(detailsRow).display === 'none';
                detailsRow.style.display = isHidden ? 'table-row' : 'none';
            }
        }
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            const modal = document.querySelector('body > #modalFactura[style*="flex"]');
            if (modal) closeFacturaModal(modal);
        }
    });

/* ============================================================
   6. FUNCIONES AUXILIARES (FUSIONADA: 
   ============================================================ */
function fillAndOpenModal(modal, btn) {
    const form = modal.querySelector('form');
    if (form) {
        // 1. Resetear formulario
        if (!form.dataset.originalAction) form.dataset.originalAction = form.action;
        form.reset();

        // Helpers rápidos
        const setVal = (sel, val) => { const el = modal.querySelector(sel); if(el) el.value = val; };
        
        // 2. Datos Básicos
        setVal('#modal_user_id', btn.dataset.userId || '');
        const userNameSpan = modal.querySelector('#modalUserName');
        if(userNameSpan) userNameSpan.textContent = btn.dataset.userName || 'Usuario';

        // 3. ASIGNAR PREFIJO (Vital para el Controller)
        // Lee del botón si es MEN- o EXT-.
        const prefix = btn.dataset.uidPrefix || 'EXT-';
        setVal('#modal_uid_prefix', prefix);

        // 4. CÁLCULO DE FECHA (Lógica interna)
        let fechaISO = '';
        
        if (prefix === 'MEN-') {
            // Si es MENSUALIDAD: Usamos la fecha estricta del periodo
            fechaISO = btn.dataset.date || new Date().toISOString().split('T')[0];
        } else {
            // Si es EXTRA: Calculamos Hoy + 7 días
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + 7);
            fechaISO = hoy.toISOString().split('T')[0];
        }

        // 5. VISUALIZACIÓN (Estilo Azul Simple)
        // Guardamos la fecha en el input oculto
        setVal('#modal_fecha', fechaISO);

        // Mostramos solo la fecha bonita (DD/MM/YYYY)
        const [y, m, d] = fechaISO.split('-');
        const textoFecha = modal.querySelector('#texto_fecha_vencimiento');
        
        if (textoFecha) {
            textoFecha.style.color = '#223F70'; // Azul Institucional
            textoFecha.style.fontWeight = 'bold';
            textoFecha.textContent = `${d}/${m}/${y}`; // Solo la fecha, sin mensajes extra
        }

        // 6. Periodo
        if (btn.dataset.periodId) {
            setVal('#modal_period_id', btn.dataset.periodId);
        }
    }
    
    document.body.style.overflow = 'hidden';
    modal.style.display = 'flex';
}

function closeFacturaModal(modal) {
    modal.style.display = 'none';
    document.body.style.overflow = '';
}



    function checkAndShowAlerts() {
        const dataDiv = document.getElementById('billing-alerts-data');
        if (dataDiv && !dataDiv.dataset.shown) {
            try {
                const alertas = JSON.parse(dataDiv.dataset.alerts);
                if (alertas.length > 0) {
                    let alerta = alertas.find(a => a.tipo === 'error') 
                               || alertas.find(a => a.tipo === 'warning') 
                               || alertas[0];

                    Swal.fire({
                        title: alerta.titulo,
                        text: alerta.mensaje,
                        icon: alerta.tipo,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#223F70',
                        backdrop: `rgba(0,0,0,0.5)`
                    });
                }
                dataDiv.dataset.shown = "true";
            } catch(e) { console.error(e); }
        }
    }

    function executeScrollLogic() {
        let hash = window.location.hash;
        if (!hash) {
            const params = new URLSearchParams(window.location.search);
            const fragment = params.get('_fragment');
            if (fragment) hash = '#' + fragment;
        }
        if (!hash || !hash.startsWith('#factura-target-')) return;

        const el = document.querySelector(hash);
        if (!el) return;

        if (el.tagName === 'DETAILS') el.open = true;
        let parent = el.parentElement;
        while (parent) {
            if (parent.tagName === 'DETAILS') parent.open = true;
            parent = parent.parentElement;
        }

        setTimeout(() => {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const summary = el.querySelector('summary');
            if (summary) {
                summary.style.transition = "background-color 0.5s";
                summary.style.backgroundColor = "#fff3cd";
                setTimeout(() => { summary.style.backgroundColor = ""; }, 2000);
            }
            const clean = window.location.pathname + window.location.search.replace(/[\?&]_fragment=[^&]+/,'').replace(/^&/, '?');
            history.replaceState(null, null, clean);
        }, 150);
    }
});


    function submitExport() {
        // 1. Obtener el formulario de filtros
        const form = document.querySelector('form[action="{{ route('Facturacion.index') }}"]');
        
        // 2. Guardar la acción original (la ruta index)
        const originalAction = form.action;
        
        // 3. Cambiar la acción a la ruta de exportación
        form.action = "{{ route('Facturacion.export') }}";
        
        // 4. Enviar el formulario (descarga el archivo)
        form.submit();
        
        // 5. Restaurar la acción original inmediatamente (para que el botón Filtrar siga funcionando normal)
        setTimeout(() => {
            form.action = originalAction;
        }, 100);
    }

    document.querySelectorAll('.form-eliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // 1. Detiene el envío automático

            const currentForm = this;

            Swal.fire({
                title: '¿Eliminar Factura?',
                text: "¡Esta acción no se puede deshacer!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Rojo para peligro
                cancelButtonColor: '#223F70', // Azul para cancelar
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true, // Pone el botón de cancelar primero (más seguro)
                background: '#fff',
                customClass: {
                    popup: 'animated fadeInDown' // Animación suave (opcional)
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // 2. Si el usuario dice SÍ, enviamos el formulario manualmente
                    currentForm.submit();
                }
            });
        });
    });
</script>

@stack('scripts')

</body>
</html>