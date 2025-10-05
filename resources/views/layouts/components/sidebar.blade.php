<!-- resources/views/partials/sidebar.blade.php -->
<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand">
      <!-- tu logo arriba -->
      <img src="{{ asset('images/LOGO2.png') }}" alt="Logo UHTA" class="brand-img" loading="lazy">
    </div>
  </div>

  <nav class="menu" aria-label="Menú principal">
    <ul>
      <li class="@if(request()->routeIs('profile.*')) active @endif">
        <a href="{{ route('profile.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('icons/user-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Mi Información</span>
        </a>
      </li>

      @if(Auth::user()->hasAnyRole(['docente', 'alumno', 'anfitrion', 'admin']))
      <li class="@if(request()->routeIs('course.*')) active @endif">
        <a href="{{ route('courses.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('icons/desktop-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Cursos</span>
        </a>
      </li>
      @endif

      @if(Auth::user()->hasAnyRole(['alumno', 'admin']))
      <li class="@if(request()->routeIs('billing.*')) active @endif">
        <a href="{{ route('billing.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('icons/money-bill-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Facturación</span>
        </a>
      </li>
      @endif

      @if(Auth::user()->hasRole('admin'))
      <li class="@if(request()->routeIs('admin.*')) active @endif">
        <a href="{{ route('admin.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('icons/clipboard-regular-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Control Administrativo</span>
        </a>
      </li>
      @endif

      <li class="@if(request()->routeIs('settings.*')) active @endif">
        <a href="{{ route('settings.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('icons/user-gear-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Ajustes</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="sidebar-bottom">
    <form method="POST" action="{{ route('logout') }}" style="width: 100%;">
      @csrf
      <button type="submit" class="btn-logout" aria-label="Cerrar sesión">
        <span class="icon" aria-hidden="true">
          <img src="{{ asset('icons/right-to-bracket-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
        </span>
        <span class="text">Cerrar sesión</span>
      </button>
    </form>

    <div class="brand-bottom">
      <img src="{{ asset('images/LOGO3.png') }}" alt="Logo Mundo Imperial" loading="lazy">
    </div>
  </div>
</aside>
