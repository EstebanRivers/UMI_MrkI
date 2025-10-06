<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand">
      <!-- tu logo arriba -->
      <img src="{{ asset('images/logos/logoumi.png') }}" alt="Logo UHTA" class="brand-img" loading="lazy">
      <img src="{{ asset('images/icons/gear-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">

    </div>
  </div>

  <nav class="menu" aria-label="Menú principal">
    <ul>
      <li class="@if(request()->routeIs('MiInformacion.*')) active @endif">
        <a href="{{ route('layouts.MiInformacion.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Mi Información</span>
        </a>
      </li>

      <li class="@if(request()->routeIs('Cursos.*')) active @endif">
        <a href="{{ route('layouts.Cursos.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/desktop-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Cursos</span>
        </a>
      </li>

      <li class="@if(request()->routeIs('Facturacion.*')) active @endif">
        <a href="{{ route('layouts.Facturacion.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/money-bill-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Facturación</span>
        </a>
      </li>

      @if (Auth::user() && Auth::user()->hasRole('master'))
      {{-- Solo mostrar para usuarios con rol 'master' --}}
      <li class="@if(request()->routeIs('ControlAdmin.*')) active @endif">
        <a href="{{ route('layouts.ControlAdmin.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Control Administrativo</span>
        </a>
      </li>
      @endif
      

      <li class="@if(request()->routeIs('Ajustes.*')) active @endif">
        <a href="{{ route('layouts.Ajustes.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/user-gear-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
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
          <img src="{{ asset('images/icons/right-to-bracket-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
        </span>
        <span class="text">Cerrar sesión</span>
      </button>
    </form>

    <div class="brand-bottom">
      <img src="{{ asset('images/LOGO3.png') }}" alt="Logo Mundo Imperial" loading="lazy">
    </div>
  </div>
</aside>
