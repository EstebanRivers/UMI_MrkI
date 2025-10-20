@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Lista de alumnos</h1>
        </div>
        <div class="option-carrer">
            <button id="openCreateCarrer">Agregar Carrera</button>
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
                {{-- INICIO DEL BUCLE: Aquí es donde Laravel iteraría sobre los datos de tu base de datos --}}
                {{-- Por ejemplo, si pasaste una variable '$registros' desde el controlador: --}}
                {{-- @foreach ($registros as $registro) --}}
                    <tr>
                        {{-- INSERCIÓN DE DATOS: Muestra los datos de la base de datos para cada columna --}}
                        {{-- <td>{{ $registro->columna1_db }}</td> Reemplaza 'columna1_db' con el nombre real de tu campo --}}
                        {{-- <td>{{ $registro->columna2_db }}</td> Reemplaza 'columna2_db' con el nombre real de tu campo --}}
                        {{-- <td>{{ $registro->columna3_db }}</td> Reemplaza 'columna3_db' con el nombre real de tu campo --}}
                        {{-- <td>{{ $registro->columna4_db }}</td> Reemplaza 'columna4_db' con el nombre real de tu campo --}}
                        {{-- <td>{{ $registro->columna5_db }}</td> Reemplaza 'columna5_db' con el nombre real de tu campo --}}
                        
                        {{-- COLUMNA DE ACCIONES: Contiene los 3 botones --}}
                        <td>
                            
                        </td>
                    </tr>
                {{--  @endforeach --}}
                {{-- FIN DEL BUCLE --}}

                {{-- COMENTARIO IMPORTANTE: Si no tienes datos, puedes poner una fila con un mensaje --}}
                {{--@empty($registros)
                    <tr>
                        <td colspan="7" class="text-center">No hay registros disponibles.</td>
                    </tr>
                @endempty--}}
            </tbody>
        </table>
    </div>
</div>
@endsection
