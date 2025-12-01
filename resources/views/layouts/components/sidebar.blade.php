<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand" style="margin-bottom: 5px">
      <!-- logo arriba -->
      @if(session('active_institution_logo'))
       
        <img src="{{ asset('storage/' . session('active_institution_logo')) }}" alt="Logo Institución" style="width: 100%; height: auto; max-width: 130px;" loading="lazy">
      @else
        
        <span>{{ session('active_institution_name', 'Logo') }}</span> 
      @endif
    </div>
  </div>

  <nav class="menu" aria-label="Menú principal">
    <ul>
      @php
    $user = Auth::user();
    // Definimos quiénes tienen acceso al menú desplegable
    $isStudent = $user->hasActiveRole('estudiante'); // O 'alumno', revisa cómo lo guardas en BD
    $isTeacher = $user->hasActiveRole('docente');
    $isMaster = $user->hasActiveRole('master');
    
    $universityName = 'Universidad Mundo Imperial';
    $isUniversity = (session('active_institution_name') == $universityName); 
    
    // Si es alumno o docente, tiene submenú
   $hasSubmenu = $isStudent || $isTeacher || ($isMaster && $isUniversity);
@endphp

@if($hasSubmenu)
    {{-- CASO 1: ALUMNO O DOCENTE (Menú Desplegable) --}}
    <li class="has-submenu {{ request()->routeIs('MiInformacion.*') ? 'active' : '' }}">
        <a href="#">
            <span class="icon" aria-hidden="true">
                <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="Info Icon" style="width:24px;height:24px" loading="lazy">
            </span>
            <span class="text">Mi Información</span>
        </a>
        
        {{-- Submenú --}}
        <ul class="submenu">
            {{-- Opción 1: Perfil --}}
            <li class="{{ request()->routeIs('MiInformacion.index') ? 'active-submenu' : '' }}">
                <a href="{{ route('MiInformacion.index') }}">Perfil</a>
            </li>

            {{-- Opción 2: Clases --}}
            <li class="{{ request()->routeIs('MiInformacion.clases') ? 'active-submenu' : '' }}">
                <a href="{{ route('MiInformacion.clases') }}">Clases</a>
            </li>

            {{-- Opción 3: Horario --}}
            <li class="{{ request()->routeIs('MiInformacion.horario') ? 'active-submenu' : '' }}">
                <a href="{{ route('MiInformacion.horario') }}">Horario</a>
            </li>

            {{-- Opción 4: Historial --}}
            <li class="{{ request()->routeIs('MiInformacion.historial') ? 'active-submenu' : '' }}">
                <a href="{{ route('MiInformacion.historial') }}">Historial Académico</a>
            </li>
        </ul>
    </li>

@else
    {{-- CASO 2: OTROS USUARIOS (Botón Normal Directo) --}}
    <li class="{{ request()->routeIs('MiInformacion.index') ? 'active' : '' }}">
        <a href="{{ route('MiInformacion.index') }}">
            <span class="icon" aria-hidden="true">
                <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="Info Icon" style="width:24px;height:24px" loading="lazy">
            </span>
            <span class="text">Mi Información</span>
        </a>
    </li>
@endif

      {{-- Lógica para mantener abierto el menú si estamos en index o certificados --}}
      <li class="has-submenu {{ request()->routeIs('Cursos.*') || request()->routeIs('courses.certificates.*') ? 'active open' : '' }}">
          <a href="#">
              <span class="icon" aria-hidden="true">
                  <img src="{{ asset('images/icons/desktop-solid-full.svg') }}" alt="" style="width:24px;height:24px" loading="lazy">
              </span>
              <span class="text">Cursos</span>
          </a>
          
          {{-- Submenú --}}
          <ul class="submenu">
              {{-- Opción 1: Cursos Disponibles (Ruta original) --}}
              <li class="{{ request()->routeIs('Cursos.index') ? 'active-submenu' : '' }}">
                  <a href="{{ route('Cursos.index') }}">Cursos Disponibles</a>
              </li>

              {{-- Opción 2: Mis Certificados (Nueva Ruta) --}}
              <li class="{{ request()->routeIs('courses.certificates.index') ? 'active-submenu' : '' }}">
                  <a href="{{ route('courses.certificates.index') }}">Mis Certificados</a> 
              </li>
          </ul>
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
        $user = Auth::user();
        $isMaster = $user->hasActiveRole('master');
        
        // Definimos ambas por si acaso usas una u otra
        $isAdmin = $user->hasActiveRole('control_administrativo');
        $isAdminBase = $isAdmin; // Alias para compatibilidad
        
        $userModules = $user->academicProfile->modules ?? [];

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
           @php
        $user = Auth::user();
        $isMaster = $user->hasActiveRole('master');
        
        // Ahora usamos 'control_administrativo' como rol base para todo
        
        // Módulos activados en el perfil académico (checkboxes)
        $userModules = $user->academicProfile->modules ?? [];

        $universityName = 'Universidad Mundo Imperial';
        $isUniversity = (session('active_institution_name') == $universityName);
    @endphp 

          {{-- 1. MÓDULO CONTROL ESCOLAR --}}
           @if( ($isMaster || ($isAdminBase && in_array('control_escolar', $userModules))) && $isUniversity )
                <li class="has-submenu {{ request()->routeIs('escolar.*') ? 'active open' : '' }}">
                    <a href="#">Control Escolar</a>
                        
                    {{-- SEGUNDO NIVEL DE SUBMENÚ (HIJOS) --}}
                    <ul class="submenu">
                        
                        {{-- Inscripción (Ruta corregida) --}}
                        <li class="{{ request()->routeIs('escolar.inscripcion.index') ? 'active-submenu' : '' }}">
                            <a href="{{ route('escolar.inscripcion.index') }}">Inscripción</a>
                        </li>

                        {{-- Lista de Alumnos (Ruta corregida para contexto escolar) --}}
                        <li class="{{ request()->routeIs('escolar.students.index') ? 'active-submenu' : '' }}">
                            <a href="{{ route('escolar.students.index') }}">Lista de alumnos</a>
                        </li>

                        {{-- Matrículas (Nueva ruta) --}}
                        <li class="{{ request()->routeIs('escolar.matriculas.index') ? 'active-submenu' : '' }}">
                        <a href="{{ route('escolar.matriculas.index') }}">Matrículas</a>
                        </li>
                        <li class="{{ request()->is('control/escolar/becas') ? 'active-submenu' : '' }}">
                            <a href="#">Becas</a>
                        </li>
                        <li class="{{ request()->is('control/escolar/practicas') ? 'active-submenu' : '' }}">
                            <a href="#">Prácticas profesionales</a>
                        </li>
                        <li class="{{ request()->is('control/escolar/servicio') ? 'active-submenu' : '' }}">
                            <a href="#">Servicio Social</a>
                        </li>
                        <li class="{{ request()->is('control/escolar/boletas') ? 'active-submenu' : '' }}">
                            <a href="#">Boleta de calificaciones</a>
                        </li>
                        <li class="{{ request()->is('control/escolar/titulacion') ? 'active-submenu' : '' }}">
                            <a href="#">Titulación</a>
                        </li>

                    </ul>
                </li>
            @endif

            {{-- 2. MÓDULO CONTROL ACADÉMICO (AHORA ES UN SUBMENÚ) --}}
            @if( ($isMaster || ($isAdminBase && in_array('control_academico', $userModules))) && $isUniversity )
                <li class="has-submenu {{ request()->is('control/academico/*') ? 'active open' : '' }}">
                    <a href="#">Control Académico</a> {{-- Se vuelve un toggle --}}
                    
                    {{-- SEGUNDO NIVEL DE SUBMENÚ (HIJOS) --}}
                    <ul class="submenu">
                       
                        {{-- RUTA: control.careers.index --}}
                        <li class="{{ request()->routeIs('control.careers.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('control.careers.index') }}">Carreras</a>
                        </li>

                        {{-- RUTA: control.Listas.materias.index --}}
                        <li class="{{ request()->routeIs('control.subjects.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('control.subjects.index') }}">Materias</a>
                        </li>

                        {{-- RUTA: control.Listas.members.index --}}
                        <li class="{{ request()->routeIs('control.teachers.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('control.teachers.index') }}">Lista de docentes</a>
                        </li>

                        {{-- RUTA: control.Horarios.index --}}
                        <li class="{{ request()->routeIs('control.schedules.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('control.schedules.index') }}">Horarios</a>
                        </li>

                        {{-- RUTA: control.Clases.index --}}
                        <li class="{{ request()->routeIs('control.classes.*') ? 'active-submenu' : '' }}">
                            {{-- <a href="{{ route('control.classes.index') }}">Clases</a> --}}
                        </li>

                        {{-- REUTILIZADA: control.Listas.students.index --}}
                        <li class="{{ request()->routeIs('control.students.*') ? 'active-submenu' : '' }}">
                            <a href="{{ route('control.students.index') }}">Lista de alumnos</a>
                        </li>
                        
                        <li class="{{ request()->is('control/academico/planeacion') ? 'active-submenu' : '' }}"><a href="#">Planeación escolar</a></li>
                    </ul>
                </li>
            @endif

            {{-- 3. MÓDULO PLANEACIÓN Y VINCULACIÓN (AHORA ES UN SUBMENÚ) --}}
            @if( ($isMaster || ($isAdmin && in_array('planeacion_vinculacion', $userModules))) && $isUniversity )
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