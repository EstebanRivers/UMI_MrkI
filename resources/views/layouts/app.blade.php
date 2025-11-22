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
                  <span class="user-role" style="margin-left: 0.3rem;">{{-- Mostramos el nombre del rol activo desde la sesión --}}
                      {{ session('active_role_display_name', ', Sin rol') }}
                  </span>
                  <span class="user-institution">en {{ session('active_institution_name', 'Sin institución') }}</span>
              </div>
          </div>
          <div class="context-switcher">
            {{-- Solo mostramos el botón si hay más de un contexto para elegir --}}
            @if (count($availableContexts) > 1)
                <button id="context-switcher-button" class="context-switcher-button">
                    <img src="{{ asset('images/icons/gear-solid-full.svg') }}" alt="Ajustes">
                </button>

                <div id="context-switcher-menu" class="context-switcher-menu">
                    <div class="context-switcher-header">Unidad de Negocio</div>
                    <ul>
                        {{-- Iterar sobre cada contexto disponible para el usuario --}}
                        @foreach ($availableContexts as $context)
                            <li>
                                {{-- Cada opción es un enlace a la ruta que cambia el contexto --}}
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
  
  {{-- Script para manejo móvil --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const toggle = document.getElementById('mobile-menu-toggle');
      const sidebar = document.querySelector('.sidebar');
      
      if (toggle && sidebar) {
        // Toggle sidebar en móvil
        toggle.addEventListener('click', function() {
          sidebar.classList.toggle('mobile-visible');
          sidebar.classList.toggle('mobile-hidden');
        });
        
        // Cerrar al hacer clic en el sidebar en móvil
        sidebar.addEventListener('click', function(e) {
          if (window.innerWidth <= 600 && e.target === sidebar) {
            sidebar.classList.add('mobile-hidden');
            sidebar.classList.remove('mobile-visible');
          }
        });
        
        // Inicializar estado móvil
        function handleResize() {
          if (window.innerWidth <= 600) {
            sidebar.classList.add('mobile-hidden');
            sidebar.classList.remove('mobile-visible');
          } else {
            sidebar.classList.remove('mobile-hidden', 'mobile-visible');
          }
        }
        
        window.addEventListener('resize', handleResize);
        handleResize();
      }
    });
  </script>
  @stack('scripts')

  
</body>
</html>
