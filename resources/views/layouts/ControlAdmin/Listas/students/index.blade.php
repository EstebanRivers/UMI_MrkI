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
@endsection
