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
  <title>@yield('title','Dashboard')</title>

  {{-- Preload critical resources --}}
  <link rel="preload" href="{{ asset('images/LOGO2.png') }}" as="image">
  <link rel="preload" href="{{ asset('images/LOGO3.png') }}" as="image">
  <link rel="preload" href="{{ asset('images/uhta-logo.png') }}" as="image">
  
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
  
  <div class="app-container">
    @include('components.sidebar')

    <main class="main-content" id="main-content">
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
