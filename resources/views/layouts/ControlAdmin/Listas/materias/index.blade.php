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
                            <a href="{{-- {{ route('ruta.ver', $registro->id) }} --}}" class="btn-accion btn-sm-base btn-info-base">Ver</a>
                            
                            {{-- Botón EDITAR --}}
                            <a href="{{-- {{ route('ruta.editar', $registro->id) }} --}}" class="btn-accion btn-sm-base btn-advertencia-base">Editar</a>
                            
                            {{-- Botón ELIMINAR --}}
                            <form action="{{-- {{ route('ruta.eliminar', $registro->id) }} --}}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-accion btn-sm-base btn-peligro-base" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
