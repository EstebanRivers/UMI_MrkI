@extends('layouts.app')
@section('title', 'Conceptos de Facturación - ' . session('active_institution_name'))
@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class="main-content-area">
    
    <header class="main-header">
        <h1>Conceptos y Montos</h1>
        <div class="header-actions">
            <form method="GET" action="{{ route('facturacion.conceptos.index') }}" class="search-form">
                <input type="text" name="search" placeholder="Buscar concepto..." value="{{ request('search') }}">
                <button type="submit">Buscar</button>
            </form>
            <button id="openModalBtn" class="btn-primary">
                + Agregar Concepto
            </button>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 5px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 5px;">{{ session('error') }}</div>
    @endif

    <div class="table-container">
        <table class="main-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Monto (MXN)</th>
                    <th>Descripción</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($conceptos as $item)
                    <tr>
                        <td><strong>{{ $item->concept }}</strong></td>
                        <td>${{ number_format($item->amount, 2) }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        
                        <td class="status-toggle-cell">
                            <form action="{{ route('facturacion.conceptos.toggleStatus', $item->id) }}" method="POST" class="inline-form" onsubmit="return confirm('¿Cambiar el estatus?');">
                                @csrf @method('POST')
                                <label class="switch" title="{{ $item->is_active ? 'Activo' : 'Inactivo' }}">
                                    <input type="checkbox" {{ $item->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </td>
                        
                        <td class="actions"> 
                            <a href="#" class="btn-icon btn-edit" data-id="{{ $item->id }}" data-item="{{ json_encode($item) }}"> 
                                <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar">
                            </a>
                            <form action="{{ route('facturacion.conceptos.destroy', $item->id) }}" method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar permanentemente?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-delete"><img src="{{ asset('images/icons/delete-left-solid-full.svg') }}" alt="Eliminar"></button>
                            </form>
                        </td>
                    </tr> 
                @empty
                    <tr><td colspan="6" class="text-center">No hay conceptos registrados.</td></tr>
                @endforelse 
            </tbody>
        </table>
        {{-- Paginación eliminada intencionalmente --}}
    </div>
</div>

{{-- MODAL --}}
<div id="formModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2 id="modalTitle">Nuevo Concepto</h2>
        
        <form id="modalForm" method="POST" action="">
            @csrf
            <div id="methodContainer"></div>

            <div id="modalBody">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="concept" style="font-weight: bold; display: block; margin-bottom: 5px;">Nombre del Concepto <span style="color:red">*</span></label>
                    <input type="text" id="concept" name="concept" class="form-control" placeholder="Ej. Inscripción Semestral" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="amount" style="font-weight: bold; display: block; margin-bottom: 5px;">Monto (MXN) <span style="color:red">*</span></label>
                    <div class="input-group" style="display: flex;">
                        <span style="padding: 8px; background: #eee; border: 1px solid #ccc; border-right: none; border-radius: 4px 0 0 4px;">$</span>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 0 4px 4px 0;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="description" style="font-weight: bold; display: block; margin-bottom: 5px;">Descripción</label>
                    <textarea id="description" name="description" class="form-control" rows="3" placeholder="Detalles opcionales..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                </div>

                {{-- CORRECCIÓN DEL CHECKBOX --}}
                <div class="form-group" style="margin-top: 20px; display: flex; align-items: center; justify-content: flex-start; gap: 15px;">
    <label for="is_active" style="font-weight: bold; margin: 0; cursor: pointer;">¿Concepto Activo?</label>
    
    <label class="custom-switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
        <input type="checkbox" id="is_active" name="is_active" value="1" checked style="opacity: 0; width: 0; height: 0;">
        
        {{-- Quitamos los estilos en línea del span para que el CSS funcione --}}
        <span class="slider round"></span>

        <style>
            /* Estilo base (Gris cuando está apagado) */
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc; /* Color gris por defecto */
                transition: .4s;
                border-radius: 34px;
            }

            /* El circulo blanco */
            .slider:before {
                position: absolute;
                content: "";
                height: 18px; width: 18px;
                left: 4px; bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            /* Estilo ACTIVO (Azul cuando está encendido) */
            .custom-switch input:checked + .slider {
                background-color: #0d6efd; /* <--- AQUÍ ESTÁ EL AZUL */
            }

            /* Mover el círculo cuando está activo */
            .custom-switch input:checked + .slider:before {
                transform: translateX(24px);
            }
        </style>
    </label>
</div>
            
            <button type="submit" class="btn-primary" style="margin-top: 20px; width: 100%; padding: 10px;">Guardar</button>
        </form>
    </div>
</div>

<script>
   (function initConceptosScript() {
        const modal = document.getElementById('formModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalForm = document.getElementById('modalForm');
        const methodContainer = document.getElementById('methodContainer');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModal = document.querySelector('.close-modal');
        
        // RUTAS
        const storeUrl = "{{ route('facturacion.conceptos.store') }}";
        // CORRECCIÓN: Usamos url() en lugar de route() para evitar el error de parámetro
        const baseUrl = "{{ url('facturacion/conceptos') }}"; 
        
        function resetForm() {
            modalForm.reset();
            methodContainer.innerHTML = ''; 
            modalForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalForm.querySelectorAll('.text-danger').forEach(el => el.remove());
            const check = document.getElementById('is_active');
            if(check) check.checked = true;
        }

        if(openModalBtn) {
            openModalBtn.addEventListener('click', function () {
                modalTitle.textContent = "Agregar Concepto";
                resetForm();
                modalForm.action = storeUrl; 
                modal.style.display = 'block';
            });
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                
                const data = JSON.parse(this.dataset.item);
                const itemId = this.dataset.id;
                
                modalTitle.textContent = `Editar Concepto #${itemId}`;
                resetForm();

                // CORRECCIÓN: Construimos la URL manualmente
                modalForm.action = `${baseUrl}/${itemId}`;
                
                methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                document.getElementById('concept').value = data.concept;
                document.getElementById('amount').value = data.amount;
                document.getElementById('description').value = data.description || '';
                
                const check = document.getElementById('is_active');
                if(check) check.checked = (data.is_active == 1);

                modal.style.display = 'block';
            });
        });

        modalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // 1. OBTENER EL BOTÓN Y DESHABILITARLO
            const submitBtn = modalForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerText = "Guardando..."; // Feedback visual opcional

            // Limpieza de errores previos
            modalForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            modalForm.querySelectorAll('.text-danger').forEach(el => el.remove());

            const formData = new FormData(modalForm);
            
            try {
                const response = await fetch(modalForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    // 2. SI FALLA, REACTIVAR EL BOTÓN
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Guardar";

                    if (response.status === 422) {
                        const data = await response.json();
                        Object.keys(data.errors).forEach(field => {
                            const input = modalForm.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const msg = document.createElement('div');
                                msg.className = 'text-danger';
                                msg.style.fontSize = '0.85em';
                                msg.style.marginTop = '5px';
                                msg.style.color = '#dc3545';
                                msg.innerText = data.errors[field][0];
                                input.parentNode.appendChild(msg);
                            }
                        });
                    } else {
                        alert('Ocurrió un error inesperado.');
                    }
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión.');
                
                // 3. SI HAY ERROR DE RED, REACTIVAR EL BOTÓN
                submitBtn.disabled = false;
                submitBtn.innerText = "Guardar";
            }
        });

        if(closeModal){
            closeModal.addEventListener('click', () => { modal.style.display = 'none'; });
        }
        window.addEventListener('click', (event) => {
            if (event.target == modal) { modal.style.display = 'none'; }
        });
    })();
</script>
@endsection