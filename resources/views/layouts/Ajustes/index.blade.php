@extends('layouts.app')

@section('title', 'Ajustes - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')

<div class="main-content-area">
    
    {{-- Cabecera con título y botones --}}
    <header class="main-header">
        <h1>{{ $page_title }}</h1>
        <div class="header-actions">
            @if($seccion === 'users')
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 5px;">
    <strong>🔍 Debug Info:</strong><br>
    Total usuarios: {{ $data->total() ?? 0 }}<br>
    Usuarios en página: {{ $data->count() ?? 0 }}<br>
    Institución activa: {{ session('active_institution_id') }} - {{ session('active_institution_name') }}<br>
    @if($data->count() > 0)
        <strong>Primer usuario:</strong><br>
        - ID: {{ $data->first()->id }}<br>
        - Nombre: {{ $data->first()->nombre }}<br>
        - Roles: {{ $data->first()->roles->pluck('name')->implode(', ') }}<br>
        - Instituciones: {{ $data->first()->institutions->pluck('name')->implode(', ') }}
    @endif
</div>
@endif
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
                        <th>Nombre del Periodo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estatus</th>
                    @elseif ($seccion === 'users')
                        <th>ID</th> 
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol Activo</th>
                        <th>Institución(es)</th> {{-- <-- AÑADIR ESTA LÍNEA --}}
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
                            <td>{{ $item->name }}</td>
                            <td>{{ optional($item->start_date)->format('d/m/Y') }}</td>
                            <td>{{ optional($item->end_date)->format('d/m/Y') }}</td>
                            <td>
                                @if($item->is_active)
                                    <span class="status-active">Activo</span>
                                @else
                                    <span class="status-inactive">Inactivo</span>
                                @endif
                            </td>
                        @elseif ($seccion === 'users')
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->nombre }} {{ $item->apellido_paterno }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->roles->first()->display_name ?? 'Sin Rol' }}</td>
                            <td>
                                @if ($item->institutions->isNotEmpty())
                                    @foreach ($item->institutions as $institution)
                                        {{-- Mostramos solo la institución activa --}}
                                        @if($institution->id == session('active_institution_id'))
                                            <span class="badge">{{ $institution->name }}</span>
                                        @endif
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                        @endif
                        
                        <td class="actions"> 
                            <a href="#" 
                            title="Editar" 
                            class="btn-icon btn-edit"
                            data-id="{{ $item->id }}"> {{-- ✅ Agregamos data-id --}}
                                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                            </a>
                            <form action="{{ route('ajustes.destroy', ['seccion' => $seccion, 'id' => $item->id]) }}" 
                                method="POST" 
                                class="inline-form"
                                onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar" class="btn-icon">
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
                {{-- El contenido del formulario se inyectará aquí con JavaScript --}}
            </div>
            <button type="submit" class="btn-primary">Guardar</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Variables ---
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        const modalForm = document.getElementById('modalForm');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModal = document.querySelector('.close-modal');

        // Variables de Blade pasadas a JS
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
            
            // 1. Limpiar @method('PUT') si existiera
            clearMethodInput();

            // 2. Establecer la ruta de 'store'
            // Asume una ruta POST como /ajustes/institutions
            modalForm.action = `${baseUrl}/${seccion}`; 
            
            // 3. Cargar el formulario vacío
            try {
                // Asume una ruta GET como /ajustes/institutions/create-form
                const response = await fetch(`${baseUrl}/${seccion}/create-form`);
                if (!response.ok) throw new Error('Error al cargar el formulario');
                
                modalBody.innerHTML = await response.text();
                modal.style.display = 'block';
            } catch (error) {
                console.error('Error en fetch (create):', error);
                alert('No se pudo cargar el formulario.');
            }
        });

        // --- Abrir modal para EDITAR ---
        // Se añade el listener a todos los botones de editar
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', async function (e) {
                e.preventDefault();
                
                // 1. Obtener el ID del item desde la fila de la tabla
                const itemId = e.currentTarget.dataset.id; // ✅ Usar data-id
                if (!itemId) {
                    console.error('No se encontró el ID del item');
                    return;
                }

                modalTitle.textContent = `Editar ${singularName} #${itemId}`;

                // 2. Establecer la ruta de 'update'
                // Asume una ruta PUT/PATCH como /ajustes/institutions/1
                modalForm.action = `${baseUrl}/${seccion}/${itemId}`;
                
                // 3. Añadir el campo _method('PUT')
                clearMethodInput(); // Limpia por si acaso
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                modalForm.prepend(methodInput); // Añade al inicio del form

                // 4. Cargar el formulario con datos
                try {
                    // Asume una ruta GET como /ajustes/institutions/1/edit-form
                    const response = await fetch(`${baseUrl}/${seccion}/${itemId}/edit-form`);
                    if (!response.ok) throw new Error('Error al cargar el formulario de edición');

                    modalBody.innerHTML = await response.text();
                    modal.style.display = 'block';
                } catch (error) {
                    console.error('Error en fetch (edit):', error);
                    alert('No se pudo cargar el formulario de edición.');
                }
            });
        });

        // --- Cerrar modal ---
        closeModal.addEventListener('click', () => {
            modal.style.display = 'none';
            modalBody.innerHTML = ''; // Limpia el contenido al cerrar
        });

        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
                modalBody.innerHTML = ''; // Limpia el contenido al cerrar
            }
        });
    });
</script>
@endpush