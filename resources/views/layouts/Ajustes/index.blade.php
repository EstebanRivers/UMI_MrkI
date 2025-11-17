@extends('layouts.app')
@section('title', 'Ajustes - ' . session('active_institution_name'))
@vite(['resources/css/courses.css', 'resources/js/app.js'])
@section('content')
<div class="main-content-area">
    
    {{-- Cabecera con título y botones --}}
    <header class="main-header">
        <h1>{{ $page_title }}</h1>
        <div class="header-actions">
            <form method="GET" action="{{ route('ajustes.show', ['seccion' => $seccion]) }}" class="search-form">
                <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}">
                <button type="submit">Buscar</button>
            </form>
            <button id="openModalBtn" class="btn-primary">
                + Agregar {{ $singular_title }}
            </button>
        </div>
    </header>
    {{-- Contenedor de la tabla --}}
    <div class="table-container">
        <table class="main-table">
            <thead>
                <tr>
                    @if ($seccion === 'institutions')
                        <th>ID</th>
                        <th>Nombre de Unidad</th>
                        <th>Logo</th>
                    @elseif ($seccion === 'departments')
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Unidad de Negocio</th>
                    @elseif ($seccion === 'workstations')
                        <th>ID</th>
                        <th>Nombre del Puesto</th>
                        <th>Departamento</th>
                    @elseif ($seccion === 'periods')
                        <th>ID</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Mensualidades</th>
                        <th>Estatus</th>
                    @elseif ($seccion === 'users')
                        <th>Unidad de Negocio</th>
                        <th>Nombre</th>
                        <th>A. Paterno</th>
                        <th>A. Materno</th>
                        <th>Usuario (RFC)</th>
                        <th>Rol</th>
                    @endif
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                    <tr>
                        @if ($seccion === 'institutions')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->logo_path)
                                    <img src="{{ asset('storage/' . $item->logo_path) }}" alt="Logo" class="table-logo">
                                @else
                                    <span>Sin logo</span>
                                @endif
                            </td>
                        @elseif ($seccion === 'departments')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->institution->name ?? 'N/A' }}</td> 
                        @elseif ($seccion === 'workstations')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->department->name ?? 'N/A' }}</td>
                        @elseif ($seccion === 'periods')
                            <td>{{ $item->id }}</td>
                            <td>{{ optional($item->start_date)->format('M Y') }}</td>
                            <td>{{ optional($item->end_date)->format('M Y') }}</td>
                            <td>{{ $item->monthly_payments_count ?? 'N/A' }}</td>
                           <td class="status-toggle-cell">
                                <div class="status-content-wrapper">
                                  
                                    <form action="{{ route('ajustes.periods.toggleStatus', $item->id) }}" 
                                          method="POST" 
                                          class="inline-form"
                                          onsubmit="return confirm('¿Estás seguro de cambiar el estatus de este periodo?');">
                                        @csrf
                                        @method('POST')
                                        
                                        <label class="switch" title="{{ $item->is_active ? 'Activo' : 'Inactivo' }}">
                                            <input 
                                                type="checkbox" 
                                                {{ $item->is_active ? 'checked' : '' }}
                                                onchange="this.form.submit()" 
                                            >
                                            <span class="slider"></span>
                                        </label>
                                    </form>

                                  
                                    
                                </div> 
                            </td>
                        @elseif ($seccion === 'users')
                            <td>{{ $item->institutions->first()->name ?? 'N/A' }}</td>
                            <td>{{ $item->nombre }}</td>
                            <td>{{ $item->apellido_paterno }}</td>
                            <td>{{ $item->apellido_materno }}</td>
                            <td>{{ $item->RFC }}</td>
                            <td>{{ $item->roles->first()->display_name ?? 'Sin Rol' }}</td>
                            
                        @endif
                        
                        {{-- =================================================== --}}
                        {{-- BLOQUE DE ACCIONES  --}}
                        {{-- =================================================== --}}
                        <td class="actions"> 
                            
                            {{-- 1. Botón "Ver" --}}
                            <a href="#" 
                                title="Ver" 
                                class="btn-icon btn-view"
                                data-id="{{ $item->id }}">
                                <img src="{{ asset('images/icons/eye-solid-full.svg') }}" alt="Ver"> 
                            </a>

                            {{-- 2. Botón "Editar" --}}
                            <a href="#" 
                                title="Editar" 
                                class="btn-icon btn-edit"
                                data-id="{{ $item->id }}"> 
                                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                            </a>
                            
                            {{-- 3. FORMULARIO DE HABILITAR/DESHABILITAR --}}
                          @if ($seccion === 'users')
                            @php
                                // Obtenemos el rol (y la data pivote) para la institución activa
                                $activeRoleForInstitution = $item->roles->first();
                                
                                // Verificamos el estatus de la tabla pivote
                                $isActiveForInstitution = @$activeRoleForInstitution->pivot->is_active ?? false;
                            @endphp

                            <form action="{{ route('ajustes.users.toggleStatus', $item->id) }}" 
                                method="POST" 
                                class="inline-form"
                                onsubmit="
                                    const isActive = {{ $isActiveForInstitution ? 'true' : 'false' }};
                                    const message = isActive 
                                        ? '¿Estás seguro de DESHABILITAR a este usuario? Ya no tendrá acceso a esta institución.' 
                                        : '¿Estás seguro de HABILITAR a este usuario para esta institución?';
                                    return confirm(message); ">
                                @csrf
                                @method('POST') 
                                
                                <label class="switch" title="{{ $isActiveForInstitution ? 'Activo' : 'Inactivo' }} (en esta Inst.)">
                                    <input 
                                        type="checkbox" 
                                        {{ $isActiveForInstitution ? 'checked' : '' }}
                                        onchange="this.form.submit()" 
                                    >
                                    <span class="slider"></span>
                                </label>
                            </form>
                        @endif

                            {{-- 4. Botón "Eliminar" --}}
                            <form action="{{ route('ajustes.destroy', ['seccion' => $seccion, 'id' => $item->id]) }}" 
                                method="POST" 
                                class="inline-form"
                                onsubmit="return confirm('ADVERTENCIA: ¿Estás seguro de ELIMINAR PERMANENTEMENTE este registro? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar Permanente" class="btn-icon">
                                    <img src="{{ asset('images/icons/trash-solid-full.svg') }}" alt="Eliminar">
                                </button>
                            </form>
                        </td>
                    </tr> 
                @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            No hay datos disponibles en la sección de {{ strtolower($page_title) }}.
                        </td>
                    </tr>
                @endforelse 
            </tbody>
        </table>
        @if($data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->hasPages())
            <div class="pagination-container">
                {{ $data->links() }}
            </div>
        @endif
    </div>
</div>
{{-- MODAL GENÉRICO --}}
<div id="formModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTitle"></h2>
        
        <form id="modalForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div id="modalBody">
                
            </div>
            <button type="submit" class="btn-primary">Guardar</button>
        </form>
    </div>
</div>


<script>
    (function initAjustesModalScript() {
        // --- Variables ---
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modalForm = document.getElementById('modalForm');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModal = document.querySelector('.close-modal');
        
        // --- Función de ayuda para ejecutar scripts ---
        function executeScriptsIn(container) {
            const scripts = container.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.textContent) {
                    newScript.textContent = oldScript.textContent;
                }
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }
        
        if (!modal || !openModalBtn) {
            return;
        }
        
        // Variables de Blade
        const seccion = @json($seccion);
        const singularName = @json($singular_title);
        const baseUrl = @json(url('ajustes'));
        
        // --- Función para limpiar el _method ---
        function clearMethodInput() {
            const oldMethodInput = modalForm.querySelector('input[name="_method"]');
            if (oldMethodInput) {
                oldMethodInput.remove();
            }
        }
        
        // --- Abrir modal para CREAR ---
        openModalBtn.addEventListener('click', async function () {
            modalTitle.textContent = `Agregar ${singularName}`;
            clearMethodInput();
            modalForm.action = `${baseUrl}/${seccion}`; 
            
            try {
                const response = await fetch(`${baseUrl}/${seccion}/create-form`);
                if (!response.ok) {
                    console.error('Respuesta de red no fue OK:', response.status, response.statusText);
                    throw new Error('Error al cargar el formulario');
                }
                modalBody.innerHTML = await response.text();
                
                
                modalBody.querySelectorAll('input, select, textarea').forEach(el => {
                   el.disabled = false;
                });
                modalForm.querySelector('button[type="submit"]').style.display = 'block';

                executeScriptsIn(modalBody); 
                modal.style.display = 'block';
            } catch (error) {
                console.error('Error en fetch (create):', error);
                alert('No se pudo cargar el formulario.');
            }
        });

       
        document.querySelectorAll('.btn-edit, .btn-view').forEach(btn => {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                
                const itemId = e.currentTarget.dataset.id;
                if (!itemId) {
                    console.error('No se encontró el ID del item');
                    return;
                }
                
                const isViewButton = e.currentTarget.classList.contains('btn-view');
                modalTitle.textContent = isViewButton 
                    ? `Ver ${singularName} #${itemId}` 
                    : `Editar ${singularName} #${itemId}`;

                modalForm.action = `${baseUrl}/${seccion}/${itemId}`;
                
                clearMethodInput(); 
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                modalForm.prepend(methodInput); 
                
                try {
                    const response = await fetch(`${baseUrl}/${seccion}/${itemId}/edit-form`);
                    if (!response.ok) {
                        console.error('Respuesta de red no fue OK:', response.status, response.statusText);
                        throw new Error('Error al cargar el formulario de edición');
                    }
                    modalBody.innerHTML = await response.text();
                    
                    if (isViewButton) {
                        modalBody.querySelectorAll('input, select, textarea').forEach(el => {
                            el.disabled = true;
                        });
                        modalForm.querySelector('button[type="submit"]').style.display = 'none';
                    } else {
                         modalBody.querySelectorAll('input, select, textarea').forEach(el => {
                            el.disabled = false;
                        });
                        modalForm.querySelector('button[type="submit"]').style.display = 'block';
                    }
                    
                    executeScriptsIn(modalBody); 
                    modal.style.display = 'block';
                } catch (error) {
                    console.error('Error en fetch (edit/view):', error);
                    alert('No se pudo cargar el formulario.');
                }
            });
        });

       
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            modalBody.innerHTML = ''; 
        });

        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
                modalBody.innerHTML = ''; 
            }
        });
    })();
</script>
@endsection