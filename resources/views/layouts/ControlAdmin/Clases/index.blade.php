@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css',
        
        'resources/js/app.js'])

@section('content')
<div class ="container">
    <!-- Header -->
    <div class ="content-header">
        <div class="content-title">
            <h1>Carreras</h1>
        </div>
    </div>
    <!-- Contenido Principal -->
    <div class="class-container">
    {{-- Formulario de Consulta (Filtros) --}}
        <form action="{{-- {{ route('nombre.de.ruta.consulta') }} --}}" method="GET" id="form-consulta"> 
            {{-- COMENTARIO: Conectar a una ruta (Controller@method) para filtrar la tabla. Usar GET. --}}
            <div class="consulta-form">
                <div class="form-group"><label for="carrera_id">Carrera:</label>
                    <select name="carrera_id" id="carrera_id" class="form-control">
                        <option value="">Seleccione una Carrera</option>
                        {{-- COMENTARIO: Aquí se debe iterar sobre las carreras obtenidas del backend (Controller) --}}
                        {{-- @foreach ($carreras as $carrera) --}}
                            <option {{-- value="{{ $carrera->id }}" {{ request('carrera_id') == $carrera->id ? 'selected' : '' }} --}}>{{--{{ $carrera->nombre }}--}}</option>
                        {{-- @endforeach --}}
                    </select>
                </div>

                <div class="option-dividido">
                    <div class="form-group"><label for="semestre_id">Semestre:</label>
                        <select name="semestre_id" id="semestre_id" class="form-control">
                            <option value="">Seleccione un Semestre</option>
                            {{-- COMENTARIO: Iterar sobre los semestres. Puede ser estático o dependiente de la carrera (usar JS/AJAX para cargar dinámicamente). --}}
                            {{-- @for ($i = 1; $i <= 10; $i++) --}}
                                <option {{-- value="{{ $i }}" {{ request('semestre_id') == $i ? 'selected' : '' }} --}}>{{-- {{ $i }}--}}</option>
                            {{-- @endfor --}}
                        </select>
                    </div>

                    <div class="form-group"><label for="materia_id">Materia:</label>
                        <select name="materia_id" id="materia_id" class="form-control">
                            <option value="">Seleccione una Materia</option>
                            {{-- COMENTARIO: Iterar sobre las materias. Idealmente, se carga dinámicamente con JS/AJAX al cambiar Carrera o Semestre. --}}
                            {{-- @foreach ($materias_filtradas as $materia)--}}
                             {{-- Asumiendo que ya están filtradas por la consulta GET --}}
                                <option {{-- value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }} --}}>{{--{{ $materia->nombre }}--}}</option>
                            {{--@endforeach--}}
                        </select>
                    </div>
                </div>
            </div>
        </form>

        {{-- Área de Tablas y Acciones --}}
        <div class="tablas-acciones">
            {{-- Primera Sección: Tabla de resultados de la consulta --}}
            <div class="tabla-consulta">
                <table class="table table-striped"><thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th> {{-- Checkbox para seleccionar todos --}}
                            <th>Código</th>
                            <th>Nombre del Item</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody id="resultado-items">
                        {{-- COMENTARIO: Aquí se iterarán los resultados de la consulta (ej. estudiantes, grupos, etc.) --}}
                        {{-- @forelse ($items_disponibles as $item)--}}
                            <tr>
                                <td><input type="checkbox" name="selected_items[]" value="{{-- {{ $item->id }} --}}" class="item-checkbox"></td>
                                <td>{{--{{ $item->codigo }}--}}</td>
                                <td>{{--{{ $item->nombre }}--}}</td>
                                <td>{{--{{ $item->estatus }}--}}</td>
                            </tr>
                        {{--@empty--}}
                            <tr>
                                <td colspan="4">No hay resultados para los filtros seleccionados.</td>
                            </tr>
                        {{-- @endforelse --}}
                    </tbody>
                </table>
                <button id="btn-agregar" class="btn btn-success" disabled>Agregar Seleccionados</button> {{-- COMENTARIO: Botón deshabilitado por defecto. Habilitar/Manejar con JS/AJAX. --}}
            </div>

            {{-- Segunda Sección: Items seleccionados y botón de Guardar --}}
            <div class="tabla-seleccionados-guardar">
                <div id="futura-tabla-seleccionados"><h3>Items a Guardar</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="items-seleccionados-body">
                            {{-- COMENTARIO: Aquí se insertarán las filas de los items seleccionados usando JavaScript/AJAX --}}
                            <tr>
                                <td colspan="3">Aún no hay items agregados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button id="btn-guardar" class="btn btn-primary" disabled>Guardar Cambios</button> {{-- COMENTARIO: Enviar los items seleccionados a una ruta de Laravel (POST) para guardar. Manejar con JS/AJAX. --}}
            </div>
        </div>
    </div>
</div>
@endsection
