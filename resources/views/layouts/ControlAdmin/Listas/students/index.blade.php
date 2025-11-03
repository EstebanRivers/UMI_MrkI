@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h1>Aulas</h1>
        </div>
        <div class="header-option">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="openModalBtn" class="mi-boton">Agregar Alumno</button>
            @endif
        </div>

        <div id="createFacilityModal" class="modal-overlay">
            <div class="modal-content-container">
                <div class="modal-header-custom">
                    <h5 id="createFacilityModalLabel">Agregar Nuevo Alumno</h5>
                    <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
                </div>
                <div class="modal-body-custom" id="modalBodyContent">
                    <div class="text-center">Cargando...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="Table-view">
        <table class="table table-striped table-bordered">
            <thead class="thead">
                <tr>
                    <th>Carrera</th>
                    <th>Nombre</th>
                    <th>Apellido Paterno</th>
                    <th>Apellido Materno</th>
                    <th>Status</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="tbody">
                @foreach ($dataList as $user)
                    <td>{{ $user->academicProfile?->carrera ?? 'Sin datos' }}</td>
                    <td>{{ $user->nombre }}</td>
                    <td>{{ $user->apellido_paterno }}</td>
                    <td>{{ $user->apellido_materno }}</td>
                    <td>{{ $user->academicProfile?->status ?? 'Sin datos' }}</td>
                    <td>
                        <a href="{{-- {{ route('ruta.ver', $registro->id) }} --}}" class="btn btn-sm btn-info">Ver</a>
                        <a href="{{-- {{ route('ruta.editar', $registro->id) }} --}}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{-- {{ route('ruta.eliminar', $registro->id) }} --}}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</button>
                        </form>
                    </td>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('createFacilityModal');
        const openBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const modalBodyContent = document.getElementById('modalBodyContent');

        // Función para cargar y mostrar el modal
        const showModal = () => {
            // 1. Mostrar el modal (haciendo visible el overlay CSS)
            if (modal) {
                modal.classList.add('is-visible');
            }

            // Opcional: Mostrar mensaje de carga
            if (modalBodyContent) {
                modalBodyContent.innerHTML = '<div class="text-center">Cargando...</div>';
            }

            // 2. Petición AJAX usando Axios (que ya tienes instalado)
            axios.get("{{ route('Listas.students.create.form') }}")
                .then(response => {
                    // 3. Inyecta el HTML del formulario
                    if (modalBodyContent) {
                        modalBodyContent.innerHTML = response.data;
                    }
                })
                .catch(error => {
                    console.error("Error al cargar el formulario:", error);
                    if (modalBodyContent) {
                        modalBodyContent.innerHTML = '<p style="color:red;">Error al cargar el formulario. Inténtalo de nuevo.</p>';
                    }
                });
        };

        // Función para ocultar el modal
        const hideModal = () => {
            if (modal) {
                modal.classList.remove('is-visible');
            }
            // Limpiar contenido al cerrar (opcional)
            if (modalBodyContent) {
                modalBodyContent.innerHTML = '';
            }
        };

        // --- Manejo de Eventos ---

        // Abrir Modal
        if (openBtn) {
            openBtn.addEventListener('click', function(event) {
                event.preventDefault(); 
                showModal();
            });
        }
        
        // Cerrar Modal con el botón (X)
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', hideModal);
        }

        // Cerrar Modal haciendo clic fuera del contenido (en el overlay)
        if (modal) {
            modal.addEventListener('click', function(event) {
                // Si el clic fue directamente en el overlay (el modal en sí)
                if (event.target === modal) { 
                    hideModal();
                }
            });
        }
    });
    </script>
@endpush
@endsection
