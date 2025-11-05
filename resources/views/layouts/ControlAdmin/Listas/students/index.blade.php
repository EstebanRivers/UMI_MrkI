@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h1>Lista de Alumnos</h1>
        </div>
        <div id="createAcademicModal" class="modal-overlay">
            <div class="modal-content-container">
                <div class="modal-header-custom">
                    <h5 id="createAcademicModalLabel">Registrar Nuevo Alumno</h5>
                    <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
                </div>
                <div class="modal-body-custom" id="modalBodyContent">
                    <div class="text-center">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
    <div class="list-header-toolbar">
        <div class="toolbar__section toolbar__section--left">
            <div class="toolbar__search">
                <input type="text" placeholder="Buscar...">
            </div>

            <div class="toolbar__actions">
                    <button class="btn btn--secondary">Exportar</button>
                    <button class="btn btn--secondary">Importar</button>
                </div>
            </div>
            
            <div class="toolbar__section toolbar__section--right">
                <div class="toolbar__actions">
                    @if(Auth::user()->hasAnyRole(['master']))
                        <button type="button" id="openModalBtn" class="btn btn--primary">Agregar Alumno</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Tablas-->
    <div class="data-table-container">
        <table class="data-table">
            <thead class="data-table-header">
                <tr>
                    <th>Carrera</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Status</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="data-table-body">
                @foreach ($dataList as $user)
                    <tr> 
                        <td data-label="Carrera">{{ $user->academicProfile?->carrera ?? 'Sin datos' }}</td>
                        <td data-label="Nombre">{{ $user->nombre }}</td>
                        <td data-label="Paterno">{{ $user->apellido_paterno }}</td>
                        <td data-label="Materno">{{ $user->apellido_materno }}</td>
                        <td data-label="Status">
                            <span class="data-status-badge data-status-{{ strtolower($user->academicProfile?->status ?? 'sin-datos') }}">
                                {{ $user->academicProfile?->status ?? 'Sin datos' }}
                            </span>
                        </td>
                        <td data-label="Acciones" class="data-actions-cell">
                            <a href="#" class="data-action-btn data-btn-view"><img src="{{asset('images/icons/eye-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy"></a>
                            <a href="#" class="data-action-btn data-btn-edit"><img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy"></a>
                            <form action="#" method="POST" class="data-action-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="data-action-btn data-btn-delete" onclick="return confirm('¿Estás seguro de eliminar este registro?')"><img src="{{asset('images/icons/Vector.svg')}}" alt="" style="width:38;height:25px" loading="lazy"></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('createAcademicModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const modalBodyContent = document.getElementById('modalBodyContent');
            
            // 💡 DEFINICIÓN DE RUTAS (CRÍTICO)
            // Estas variables globales son usadas por las funciones handleUserSelect y handleFormSubmit
            window.userDataUrlTemplate = '{{ route('Listas.students.user.data', ['user' => 'USER_ID_PLACEHOLDER'], false) }}';
            window.storeUrl = '{{ route('Listas.students.store') }}';
            window.createUrl = '{{ route('Listas.students.create') }}';

            // --- 1. Funciones de Control del Modal (Accesibles Globalmente) ---
            
            const hideModal = () => {
                if (modal) { modal.classList.remove('is-visible'); }
                document.body.style.overflow = ''; 
                // Limpiar contenido para forzar la recarga
                if (modalBodyContent) { modalBodyContent.innerHTML = '<div class="text-center">Cargando...</div>'; }
            };
            window.hideModal = hideModal; 

            const showModal = () => {
                document.body.style.overflow = 'hidden'; 
                if (modal) { modal.classList.add('is-visible'); }
                
                // Carga el formulario HTML
                axios.get(window.createUrl)
                    .then(response => {
                        if (modalBodyContent) { modalBodyContent.innerHTML = response.data; }
                    })
                    .catch(error => {
                        console.error("Error al cargar el formulario:", error);
                        if (modalBodyContent) { modalBodyContent.innerHTML = '<p style="color:red;">Error al cargar el formulario. Inténtalo de nuevo.</p>'; }
                    });
            };

            // --- 2. Lógica del Formulario: Delegación de Eventos ---
            
            // A. Delegación del Evento CHANGE (Selección de Usuario)
            modal.addEventListener('change', function(event) {
                if (event.target.id === 'user_selector') {
                    // Llama a la función externa, pasándole el ID y la plantilla de URL
                    // NOTA: handleUserSelect debe estar definido en el archivo externo
                    handleUserSelect(event.target.value, window.userDataUrlTemplate);
                }
            });

            // B. Delegación del Evento SUBMIT (Envío del Formulario)
            modal.addEventListener('submit', function(event) {
                if (event.target.id === 'createAcademicForm') {
                    event.preventDefault(); 
                    // Llama a la función externa
                    handleFormSubmit(event.target);
                }
            });

            // --- 3. Eventos de Control de Modal (Apertura y Cierre) ---
            if (openBtn) { openBtn.addEventListener('click', function(event) { event.preventDefault(); showModal(); }); }
            if (closeModalBtn) { closeModalBtn.addEventListener('click', hideModal); }
            if (modal) { modal.addEventListener('click', function(event) { if (event.target === modal) { hideModal(); } }); }
            document.addEventListener('keydown', function(event) { if (event.key === 'Escape' && modal.classList.contains('is-visible')) { hideModal(); } });
        });
    </script>
@endpush
@endsection
