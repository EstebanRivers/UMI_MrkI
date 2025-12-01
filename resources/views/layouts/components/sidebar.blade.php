<aside class="sidebar" role="navigation" aria-label="Menú de navegación principal">
  <div class="sidebar-top">
    <div class="brand" style="margin-bottom: 5px">
      <!-- logo arriba -->
      @if(session('active_institution_logo'))
<<<<<<< HEAD
       
        <img src="{{ asset('storage/' . session('active_institution_logo')) }}" alt="Logo Institución" style="width: 100%; height: auto; max-width: 130px;" loading="lazy">
      @else
        
        <span>{{ session('active_institution_name', 'Logo') }}</span> 
      @endif
    </div>
  </div>

    {{-- =================================================================== --}}
    {{-- MENÚ LATERAL --}}
    {{-- =================================================================== --}}
    <nav class="menu" aria-label="Menú principal">
        <ul>

            {{-- 1. MI INFORMACIÓN --}}
            @if($hasInfoSubmenu)
                <li class="has-submenu {{ request()->routeIs('MiInformacion.*') ? 'active' : '' }}">
                    <a href="#">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/user-solid-full.svg') }}" alt="Info Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Mi Información</span>
                    </a>
                    <ul class="submenu">
                        <li class="{{ request()->routeIs('MiInformacion.index') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.index') }}">Perfil</a>
                        </li>
                        <li class="{{ request()->routeIs('MiInformacion.clases') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.clases') }}">Clases</a>
                        </li>
                        <li class="{{ request()->routeIs('MiInformacion.horario') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.horario') }}">Horario</a>
                        </li>
                        <li class="{{ request()->routeIs('MiInformacion.historial') ? 'active-submenu' : '' }}">
                            <a href="{{ route('MiInformacion.historial') }}">Historial Académico</a>
                        </li>
                        @if($isDocenteGroup)
                            <li class="{{ request()->routeIs('MiInformacion.boletas') ? 'active-submenu' : '' }}">
                                <a href="#">Boletas</a>
                            </li>
                        @endif
                        @if($isStudentGroup)
                             <li class="{{ request()->routeIs('MiInformacion.reticula') ? 'active-submenu' : '' }}">
                                <a href="#">Retícula Escolar</a>
                            </li>
                             <li class="{{ request()->routeIs('MiInformacion.boletas') ? 'active-submenu' : '' }}">
                                <a href="#">Boleta de Calif.</a>
                            </li>
                        @endif
                         @if($isMaster && $isUniversity)
                            <li class="{{ request()->routeIs('MiInformacion.constancias') ? 'active-submenu' : '' }}">
                                <a href="#">Constancias</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @else
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
        
=======
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
        
>>>>>>> parent of 0358ee6 (Fix: Reemplazo forzoso de Proyecto)
        {{-- ----------------------------------------- --}}
        {{-- INICIA EL PRIMER NIVEL DE SUBMENÚ (FLOTANTE) --}}
        {{-- ----------------------------------------- --}}
        <ul class="submenu">
<<<<<<< HEAD
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

                        @if($canSeeAcademico)
                            <li class="has-submenu {{ request()->routeIs('control.*') && !request()->routeIs('control.planeacion.*') ? 'active open' : '' }}">
                                <a href="#">Control Académico</a>
                                <ul class="submenu">
                                    <li class="{{ request()->routeIs('control.careers.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.careers.index') }}">Carreras</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.subjects.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.subjects.index') }}">Materias</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.teachers.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.teachers.index') }}">Lista de Docentes</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.schedules.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.schedules.index') }}">Horarios</a>
                                    </li>
                                    <li class="{{ request()->routeIs('control.classes.*') ? 'active-submenu' : '' }}">
                                        <a href="#">Clases</a> 
                                    </li>
                                    <li class="{{ request()->routeIs('control.students.*') ? 'active-submenu' : '' }}">
                                        <a href="{{ route('control.students.index') }}">Lista de Alumnos</a>
                                    </li>
                                    <li class="{{ request()->is('control/academico/reticula') ? 'active-submenu' : '' }}">
                                        <a href="#">Retícula Escolar</a>
                                    </li>
                                    <li class="{{ request()->is('control/academico/planeacion') ? 'active-submenu' : '' }}">
                                        <a href="#">Planeación Escolar</a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        @if($canSeePlaneacion)
                            <li class="has-submenu {{ request()->is('control/planeacion/*') ? 'active open' : '' }}">
                                <a href="#">Planeación y Vinc.</a>
                                <ul class="submenu">
                                    <li class="{{ request()->is('control/planeacion/general') ? 'active-submenu' : '' }}">
                                        <a href="#">Información</a>
                                    </li>
                                    <li class="{{ request()->is('control/planeacion/presupuestos') ? 'active-submenu' : '' }}">
                                        <a href="#">Presupuestos</a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif

            {{-- 5. AJUSTES --}}
            @if($showSettings)
                <li class="has-submenu {{ request()->routeIs('ajustes.*') ? 'active open' : '' }}">
                    <a href="#">
                        <span class="icon" aria-hidden="true">
                            <img src="{{ asset('images/icons/user-gear-solid-full.svg') }}" alt="Ajustes Icon" style="width:24px;height:24px" loading="lazy">
                        </span>
                        <span class="text">Ajustes</span>
                    </a>
                    <ul class="submenu">
                        @if($isMaster)
                            @if($isUniversity)
                                <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                                </li>
                                <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                                    <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
                                </li>
                            @else
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
                            <li class="{{ request()->is('ajustes/users') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'users') }}">Usuarios</a>
                            </li>
                        @elseif($isControlGroup)
                            <li class="{{ request()->is('ajustes/users') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'users') }}">Usuarios</a>
                            </li>
                            <li class="{{ request()->is('ajustes/periods') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'periods') }}">Periodos</a>
                            </li>
                            <li class="{{ request()->is('ajustes/departments') ? 'active-submenu' : '' }}">
                                <a href="{{ route('ajustes.show', 'departments') }}">Departamentos</a>
                            </li>
                        @endif
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

=======
            
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

>>>>>>> parent of 0358ee6 (Fix: Reemplazo forzoso de Proyecto)
    <div class="brand-bottom">
      <img src="{{ asset('images/LOGO3.png') }}" alt="Logo Mundo Imperial" loading="lazy">
    </div>
  </div>
</aside>