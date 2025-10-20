@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Lista de Materias</h1>
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
                    <th>No. Créditos</th>
                    <th>Semestre</th>
                    <th>Modalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="tbody">
                @foreach ($dataList as $registro)
                    <td>{{ $registro->career?->name ?? 'Sin datos'}}</td>
                    <td>{{ $registro->nombre ?? 'Sin datos'}}</td>
                    <td>{{ $registro->creditos ?? 'Sin datos'}}</td>
                    <td>{{ $registro->semestre ?? 'Sin datos'}}</td>
                    <td>{{ $registro->type ?? 'Sin datos'}}</td>
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
@endsection
