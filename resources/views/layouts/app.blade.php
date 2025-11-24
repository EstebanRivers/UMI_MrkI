<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
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

{{-- Script 2: LÓGICA FACTURACIÓN (SCROLL, LIMPIEZA Y MODAL) --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
        
        // 1. INICIALIZACIÓN
        executeScrollLogic();
        setTimeout(executeScrollLogic, 500); 
        
        // 2. OBSERVADOR SPA
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            const observer = new MutationObserver(() => setTimeout(executeScrollLogic, 300));
            observer.observe(mainContent, { childList: true, subtree: true });
        }

        // 3. DELEGACIÓN DE EVENTOS
        document.addEventListener('click', (e) => {
            // A. ABRIR MODAL (Solo botones con la clase NUEVA)
            const addBtn = e.target.closest('.js-trigger-factura');
            if (addBtn) {
                e.preventDefault();
                let newModal = document.querySelector('#main-content #modalFactura');
                const zombieModal = document.querySelector('body > #modalFactura');

                if (newModal) {
                    if (zombieModal) zombieModal.remove();
                    document.body.appendChild(newModal);
                } else if (zombieModal) {
                    newModal = zombieModal;
                }

                if (newModal) fillAndOpenModal(newModal, addBtn);
                return;
            }

            // B. CERRAR MODAL
            if (e.target.closest('.close') || e.target.classList.contains('modal')) {
                const modal = e.target.closest('.modal');
                if (modal && modal.id === 'modalFactura') closeFacturaModal(modal);
            }

            // C. TOGGLE DETALLES
            const toggleBtn = e.target.closest('.icon-toggle');
            if (toggleBtn) {
                const row = toggleBtn.closest('tr');
                const detailsRow = row.nextElementSibling;
                if (detailsRow) {
                    const isHidden = window.getComputedStyle(detailsRow).display === 'none';
                    detailsRow.style.display = isHidden ? 'table-row' : 'none';
                }
            }
        });

        // ESCAPE
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.querySelector('body > #modalFactura[style*="flex"]');
                if (modal) closeFacturaModal(modal);
            }
        });

        // === 4. LÓGICA MAESTRA DE SCROLL Y LIMPIEZA ===
        function executeScrollLogic() {
            let hash = window.location.hash;
            
            // PLAN B: Si la URL llegó sucia (?_fragment=...), la convertimos a hash
            if (!hash) {
                const urlParams = new URLSearchParams(window.location.search);
                const fragment = urlParams.get('_fragment');
                if (fragment) hash = '#' + fragment;
            }

            // Filtro de seguridad (Solo actuamos si es target de factura)
            if (!hash || !hash.startsWith('#target-')) return;

            try {
                const targetElement = document.querySelector(hash);
                
                if (targetElement) {
                    console.log('📍 Objetivo encontrado:', hash);

                    // 1. Abrir Acordeones
                    if (targetElement.tagName === 'DETAILS') targetElement.open = true;

                    let parent = targetElement.parentElement;
                    while (parent) {
                        if (parent.tagName === 'DETAILS') parent.open = true;
                        parent = parent.parentElement;
                    }

                    // 2. Scroll y Resalte
                    setTimeout(() => {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        const summary = targetElement.querySelector('summary');
                        if(summary) {
                            summary.style.transition = "background-color 0.5s";
                            summary.style.backgroundColor = "#fff3cd"; 
                            setTimeout(() => { summary.style.backgroundColor = ""; }, 2000);
                        }

                        // === 3. LIMPIEZA DE URL (MÁGICO) ===
                        // Esto borra el #target de la barra de direcciones para que quede limpia
                        const cleanUrl = window.location.pathname + window.location.search.replace(/[\?&]_fragment=[^&]+/, '').replace(/^&/, '?');
                        history.replaceState(null, null, cleanUrl);

                    }, 150);
                }
            } catch (e) { console.error(e); }
        }

        // --- FUNCIONES AUXILIARES ---
        function fillAndOpenModal(modal, btn) {
            const form = modal.querySelector('form');
            if (form) {
                if (!form.dataset.originalAction) form.dataset.originalAction = form.action;
                form.reset(); 
                const setVal = (sel, val) => { const el = modal.querySelector(sel); if(el) el.value = val; };
                const setText = (sel, val) => { const el = modal.querySelector(sel); if(el) el.textContent = val; };

                setVal('#modal_user_id', btn.dataset.userId || '');
                setText('#modalTitle', `Agregar Factura a: ${btn.dataset.userName || 'Usuario'}`);
                
                const fechaVal = btn.dataset.date || new Date().toISOString().split('T')[0];
                setVal('#modal_fecha', fechaVal);
                const [y, m, d] = fechaVal.split('-');
                setText('#texto_fecha_vencimiento', `${d}/${m}/${y}`);

                if (btn.dataset.periodId) setVal('#modal_period_id', btn.dataset.periodId);
            }
            document.body.style.overflow = 'hidden'; 
            modal.style.display = 'flex';
        }

        function closeFacturaModal(modal) {
            modal.style.display = 'none';
            document.body.style.overflow = ''; 
        }
    });
  </script>
  @stack('scripts')


</body>
</html>