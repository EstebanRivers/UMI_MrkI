@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h1>Aulas</h1>
        </div>
        <div class="header-option">
            @if(Auth::user()->hasAnyRole(['master']))
                <button type="button" id="openModalBtn" class="mi-boton">Agregar Aula</button>
            @endif
        </div>

        <div id="createFacilityModal" class="modal-overlay">
            <div class="modal-content-container">
                <div class="modal-header-custom">
                    <h5 id="createFacilityModalLabel">Agregar Nueva Aula</h5>
                    <button type="button" id="closeModalBtn" class="close-custom">&times;</button>
                </div>
                <div class="modal-body-custom" id="modalBodyContent">
                    <div class="text-center">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Aulas -->
    @forelse ($data as $item)
        <div class="aula-container">
            <div class="aula-info">
                <div class="aula-name">
                    <h3>{{$item->numero_aula}}</h3>
                </div>
                <div class="aula-data">
                    <p>Capacidad: {{$item->capacidad}}</p>
                </div>
                <div class="aula-buttons">
                    <form action="{{ route('Facilities.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta aula?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete">
                            <img src="{{asset('images/icons/Vector.svg')}}" alt="" style="width:38;height:25px" loading="lazy">
                        </button>
                    </form>
                </div>
            </div>
            <div class="aula-picture">

            </div>
        </div>
    @empty
        
    @endforelse
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
            axios.get("{{ route('Facilities.create.form') }}")
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
    // Se ejecuta DESPUÉS de que el formulario se inyecta en el modal
    document.addEventListener('submit', function(event) {
        
        // 1. Verificamos si el formulario que se está enviando es el nuestro
        if (event.target && event.target.id === 'createFacilityForm') {
            
            event.preventDefault(); // ¡Detenemos el envío normal que recarga la página!
            
            const form = event.target;
            const formData = new FormData(form); // Recolecta todos los datos del formulario
            const messagesDiv = document.getElementById('formMessages');
            
            // Muestra un mensaje de carga
            messagesDiv.innerHTML = '<p style="color: blue;">Guardando...</p>';

            // 2. Envío de datos a Laravel usando Axios
            axios.post('/aulas', formData) // Asegúrate de que esta URL sea la correcta para tu Store/Guardar
                .then(response => {
                    // Éxito: El servidor devolvió 200/201
                    messagesDiv.innerHTML = '<p style="color: green;">¡Aula guardada con éxito!</p>';
                    
                    // Limpia y cierra el modal después de un breve retraso
                    setTimeout(() => {
                        hideModal(); 
                        // Opcional: Recarga tu tabla de aulas o solo la sección de la lista
                        // window.location.reload(); 
                    }, 1500);
                })
                .catch(error => {
                    // Fracaso: El servidor devolvió 422 (errores de validación) o 500
                    messagesDiv.innerHTML = '<p style="color: red;">Error al guardar. Verifica los campos.</p>';
                    console.error(error.response);
                    
                    if (error.response && error.response.status === 422) {
                        // Muestra errores de validación
                        let errorsHtml = '<ul>';
                        Object.values(error.response.data.errors).forEach(messages => {
                            messages.forEach(message => {
                                errorsHtml += `<li style="color: red;">${message}</li>`;
                            });
                        });
                        errorsHtml += '</ul>';
                        messagesDiv.innerHTML = errorsHtml;
                    }
                });
        }
    });
</script>
@endpush
@endsection
