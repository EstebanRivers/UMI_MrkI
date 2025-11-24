<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand" style="margin-bottom: 5px">
      <!-- logo arriba -->
      @if(session('active_institution_logo'))
        {{-- Si hay un logo en la sesión, lo muestra --}}
        <img src="{{ asset('storage/' . session('active_institution_logo')) }}" alt="Logo Institución" style="width: 100%; height: auto; max-width: 130px;" loading="lazy">
      @else
        {{-- Si no hay logo, muestra el nombre como texto --}}
        <span>{{ session('active_institution_name', 'Logo') }}</span> 
      @endif
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

    @php
    // Verificamos los roles y el contexto una sola vez
    $user = Auth::user();
    $isMaster = $user->hasActiveRole('master');
    $isAdmin = $user->hasActiveRole('control_administrativo');
    $userModules = @$user->academicProfile->modules ?? [];

    $universityName = 'Universidad Mundo Imperial';
    $isUniversity = (session('active_institution_name') == $universityName);
@endphp

{{-- 1. Botón principal: Visible si es Master/Admin Y está en la Universidad --}}
@if(($isMaster || $isAdmin) && $isUniversity)
    
    <li class="has-submenu {{ request()->routeIs('control.*') ? 'active' : '' }}">
        <a href="#">
            <span class="icon" aria-hidden="true">
                <img src="{{ asset('images/icons/clipboard-regular-full.svg') }}" alt="Control Icon" style="width:24px;height:24px" loading="lazy">
            </span>
            <span class="text">Control Administrativo</span>
        </a>
        
        {{-- ----------------------------------------- --}}
        {{-- INICIA EL PRIMER NIVEL DE SUBMENÚ (FLOTANTE) --}}
        {{-- ----------------------------------------- --}}
        <ul class="submenu">
            
            {{-- 1. MÓDULO CONTROL ESCOLAR (AHORA ES UN SUBMENÚ) --}}
            @if($isMaster || in_array('control_escolar', $userModules))
                <li class="has-submenu {{ request()->is('control/escolar/*') ? 'active open' : '' }}">
                    <a href="#">Control Escolar</a> {{-- Se vuelve un toggle --}}
                    
                    {{-- SEGUNDO NIVEL DE SUBMENÚ (HIJOS) --}}
                    <ul class="submenu">
                        <li class="{{ request()->is('control/escolar/inscripcion') ? 'active-submenu' : '' }}"><a href="#">Inscripción</a></li>
                        <li class="{{ request()->is('control/escolar/alumnos') ? 'active-submenu' : '' }}"><a href="#">Lista de alumnos</a></li>
                        <li class="{{ request()->is('control/escolar/matriculas') ? 'active-submenu' : '' }}"><a href="#">Matrículas</a></li>
                        <li class="{{ request()->is('control/escolar/becas') ? 'active-submenu' : '' }}"><a href="#">Becas</a></li>
                        <li class="{{ request()->is('control/escolar/practicas') ? 'active-submenu' : '' }}"><a href="#">Prácticas profesionales</a></li>
                        <li class="{{ request()->is('control/escolar/servicio') ? 'active-submenu' : '' }}"><a href="#">Servicio Social</a></li>
                        <li class="{{ request()->is('control/escolar/boletas') ? 'active-submenu' : '' }}"><a href="#">Boleta de calificaciones</a></li>
                        <li class="{{ request()->is('control/escolar/titulacion') ? 'active-submenu' : '' }}"><a href="#">Titulación</a></li>

                    </ul>
                </li>
            @endif

            {{-- 2. MÓDULO CONTROL ACADÉMICO (AHORA ES UN SUBMENÚ) --}}
            @if($isMaster || in_array('control_academico', $userModules))
                <li class="has-submenu {{ request()->is('control/academico/*') ? 'active open' : '' }}">
                    <a href="#">Control Académico</a> {{-- Se vuelve un toggle --}}
                    
                    {{-- SEGUNDO NIVEL DE SUBMENÚ (HIJOS) --}}
                    <ul class="submenu">
                       
                         <li class="{{ request()->is('control/academico/carreras') ? 'active-submenu' : '' }}"><a href="#">Carreras</a></li>
                        <li class="{{ request()->is('control/academico/materias') ? 'active-submenu' : '' }}"><a href="#">Materias</a></li>
                        <li class="{{ request()->is('control/academico/docentes') ? 'active-submenu' : '' }}"><a href="#">Lista de docentes</a></li>
                        <li class="{{ request()->is('control/academico/horarios') ? 'active-submenu' : '' }}"><a href="#">Horarios</a></li>
                        <li class="{{ request()->is('control/academico/clases') ? 'active-submenu' : '' }}"><a href="#">Clases</a></li>
                        <li class="{{ request()->is('control/academico/alumnos') ? 'active-submenu' : '' }}"><a href="#">Lista de alumnos</a></li>
                        <li class="{{ request()->is('control/academico/planeacion') ? 'active-submenu' : '' }}"><a href="#">Planeación escolar</a></li>
                    </ul>
                </li>
            @endif

            {{-- 3. MÓDULO PLANEACIÓN Y VINCULACIÓN (AHORA ES UN SUBMENÚ) --}}
            @if($isMaster || in_array('planeacion_vinculacion', $userModules))
                <li class="has-submenu {{ request()->is('control/planeacion/*') ? 'active open' : '' }}">
                    <a href="#">Planeación y vinculación</a> {{-- Se vuelve un toggle --}}
                    
                    {{-- SEGUNDO NIVEL DE SUBMENÚ (HIJOS) --}}
                    <ul class="submenu">
                        <li class="{{ request()->is('control/planeacion/presupuestos') ? 'active-submenu' : '' }}"><a href="#">Presupuestos</a></li>
                    </ul>
                </li>
            @endif
        </ul>
        {{-- ----------------------------------------- --}}
        {{-- FIN DEL PRIMER NIVEL DE SUBMENÚ --}}
        {{-- ----------------------------------------- --}}
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
            
            @php
                $universityName = 'Universidad Mundo Imperial';
                $isUniversity = (session('active_institution_name') == $universityName);
            @endphp

            {{-- !! LÓGICA CORREGIDA !! --}}
            @if($isUniversity)
                {{-- Si SÍ es la Uni: Deptos, Periodos --}}
                <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                    <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                </li>
                <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                    <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
                </li>
            @else
                {{-- Si NO es la Uni: Unidades, Deptos, Puestos --}}
                <li class="{{ request()->is('ajustes/institutions') ? 'active-submenu' : '' }}">
                    <a href="{{ route('ajustes.show', 'institutions') }}">Unidades de Negocio</a>
                </li>
                <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                    <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                </li>
                <li class="{{ request()->is('ajustes/workstations') ? 'active-submenu' : '' }}">
                    <a href="{{ route('ajustes.show', 'workstations') }}">Puestos</a>
                </li>
            @endif
            
            {{-- "Usuarios" siempre se muestra --}}
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