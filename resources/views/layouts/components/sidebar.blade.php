<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand" style="margin-bottom: 5px">
      <!-- logo arriba -->
      <img src="{{ asset('images/logos/' . session('active_institution_name') . '.png') }}" alt="Logo" style="width: 100%; height: auto; max-width: 130px;" loading="lazy">
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

      @if(Auth::user()->hasActiveRole('master'))
      <li class="@if(request()->routeIs('ControlAdmin.*')) active @endif">
        <a href="{{ route('ControlAdmin.index') }}">
          <span class="icon" aria-hidden="true">
            <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
          </span>
          <span class="text">Control Administrativo</span>
        </a>
      </li>
      @endif
      
      @if(Auth::user()->hasActiveRole('master'))
      <li class="has-submenu {{ request()->routeIs('ajustes.*') ? 'active open' : '' }}">
        <a href="#">
            <span class="icon" aria-hidden="true">
                <img src="{{ asset('images/icons/user-gear-solid-full.svg') }}" alt="Ajustes Icon" style="width:24px;height:24px" loading="lazy">
            </span>
            <span class="text">Ajustes</span>
        </a>
        <ul class="submenu">
            <li class="{{ request()->is('ajustes/institutions') ? 'active-submenu' : '' }}">
                <a href="{{ route('ajustes.show', 'institutions') }}">Unidades de Negocio</a>
            </li>
            <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
            </li>
            <li class="{{ request()->is('ajustes/workstations') ? 'active-submenu' : '' }}">
                <a href="{{ route('ajustes.show', 'workstations') }}">Puestos</a>
            </li>
            <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
            </li>
            <li class="{{ request()->is('ajustes/users') ? 'active-submenu' : '' }}">
                <a href="{{ route('ajustes.show', 'users') }}">Usuarios</a>
            </li>
        </ul>
    </li>
      @endif
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

