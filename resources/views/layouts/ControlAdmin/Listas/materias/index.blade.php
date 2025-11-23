@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Lista de Materias</h1>
        </div>
    </div>
    <div class="list-header-toolbar">
        <div class="toolbar__section toolbar__section--left">
            <div class="toolbar__search">
                <input type="text" placeholder="Buscar por...">
            </div>      
            <div class="toolbar__section toolbar__section--right">
                <div class="toolbar__actions">
                    @if(Auth::user()->hasAnyRole(['master']))
                        <button type="button" id="openCreateMateriaBtn" class="btn btn--primary">Agregar Materia</button>
                    @endif
                </div>
                @include('layouts.ControlAdmin.Listas.materias.create')
            </div>
        </div>
    </div>
    <div class="Table-view">
        <table class="tabla-base tabla-rayas tabla-bordes">
            <thead class="encabezado-tabla">
                <tr>
                    <th>Carrera</th>
                    <th>Nombre</th>
                    <th>No. Créditos</th>
                    <th>Semestre</th>
                    <th>Modalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="cuerpo-tabla">
                @foreach ($dataList as $registro)
                    <tr> {{-- ¡NOTA: Agregué la etiqueta <tr> faltante! --}}
                        <td>{{ $registro->career?->name ?? 'Sin datos'}}</td>
                        <td>{{ $registro->nombre ?? 'Sin datos'}}</td>
                        <td>{{ $registro->creditos ?? 'Sin datos'}}</td>
                        <td>{{ $registro->semestre ?? 'Sin datos'}}</td>
                        <td>{{ $registro->type ?? 'Sin datos'}}</td>
                        <td>
                            {{-- Botón VER --}}
                            <a href="{{-- {{ route('ruta.ver', $registro->id) }} --}}" class="data-action-btn data-btn-view"><img src="{{asset('images/icons/eye-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy"></a>
                            
                            {{-- Botón EDITAR --}}
                            <button type="button" class="data-action-btn data-btn-edit" data-materia-id="{{ $registro->id }}"><img src="{{asset('images/icons/pen-to-square-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy"></button>
                            @include('layouts.ControlAdmin.Listas.materias.edit', ['registro' => $registro, 'carreras' => $carreras])
                            {{-- Botón ELIMINAR --}}
                            <form action="{{-- {{ route('ruta.eliminar', $registro->id) }} --}}" method="POST" style="display: inline;">
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
            
            // --- 1. LÓGICA DEL MODAL DE CREACIÓN (Singular) ---
            
            // Identificadores únicos para el modal de creación
            const createModal = document.getElementById('createMateriaModal');
            const openCreateBtn = document.getElementById('openCreateMateriaBtn'); 
            
            // Función para abrir el modal de Creación
            if (openCreateBtn) {
                openCreateBtn.addEventListener('click', () => {
                    if (createModal) {
                        createModal.style.display = 'flex';
                    }
                });
            }

            // Detectar errores de Creación (Asumiendo que no hay ID en old input, solo el campo 'name')
            // CAMBIO 1: Se ajusta la verificación de la ruta a 'materias.update'
            const hasCreateErrors = @json($errors->hasAny() && old('name') && !request()->routeIs('materias.update')); 

            if (createModal && hasCreateErrors) {
                 createModal.style.display = 'flex'; 
            }
            
            // ----------------------------------------------------
            
            // --- 2. LÓGICA DEL MODAL DE EDICIÓN (Múltiple) ---
            
            // Selector de clase para TODOS los botones de edición
            const openEditButtons = document.querySelectorAll('.btn-edit');

            openEditButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Nota: Se ha cambiado a 'data-materia-id' para coincidir con la variable
                    const materiaId = this.getAttribute('data-materia-id'); 
                    // Abrir el modal específico de esta materia usando el ID único
                    const modal = document.getElementById('editMateriaModal_' + materiaId);
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                });
            });

            // Detectar errores de Edición (Asumiendo que un modal con error tiene .alert-danger)
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                const errorElement = modal.querySelector('.alert-danger'); 
                
                // CAMBIO 2: Se ajusta el prefijo del ID del modal de edición
                if (errorElement && modal.id.startsWith('editMateriaModal_')) {
                     modal.style.display = 'flex';
                }
            });


            // ----------------------------------------------------
            
            // --- 3. LÓGICA UNIFICADA DE CIERRE (para todos los modales) ---
            
            // Cierre con el botón 'X' (.close-custom) o 'Cancelar' (.btn-secondary)
            document.querySelectorAll('.close-custom, .btn-secondary').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Buscar el contenedor de modal más cercano y cerrarlo
                    const modal = btn.closest('.modal-overlay');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Cierre al hacer clic fuera del modal (overlay) y con tecla ESC
            document.querySelectorAll('.modal-overlay').forEach(modal => {
                // Cierre por clic en overlay
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Cierre con la tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    // Cierra cualquier modal que esté visible
                    document.querySelectorAll('.modal-overlay').forEach(modal => {
                         if (modal.style.display === 'flex') {
                             modal.style.display = 'none';
                         }
                    });
                }
            });
            
        });
    </script>
@endpush
@endsection
