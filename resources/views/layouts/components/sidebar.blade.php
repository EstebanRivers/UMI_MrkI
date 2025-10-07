<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand" style="margin-bottom: 5px">
      <!-- logo arriba -->
      <img src="{{ asset('images/logos/' . session('active_institution_name') . '.png') }}" alt="Logo" style="width: 100%; height: auto; max-width: 130px;"> 
    </div>
  </div>

  <nav class="menu" aria-label="Menú principal">
    <ul>
      <li class="@if(request()->routeIs('MiInformacion.*')) active @endif">
        <a href="{{ route('MiInformacion.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Mi Información</span>
        </a>
      </li>

      <li class="@if(request()->routeIs('Cursos.*')) active @endif">
        <a href="{{ route('Cursos.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/desktop-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Cursos</span>
        </a>
      </li>

      <li class="@if(request()->routeIs('Facturacion.*')) active @endif">
        <a href="{{ route('Facturacion.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/money-bill-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Facturación</span>
        </a>
      </li>

      <li class="@if(request()->routeIs('ControlAdmin.*')) active @endif">
        <a href="{{ route('ControlAdmin.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Control Administrativo</span>
        </a>
      </li>
      

      <li class="@if(request()->routeIs('Ajustes.*')) active @endif">
        <a href="{{ route('Ajustes.index') }}">
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
