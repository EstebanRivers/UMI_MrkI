@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/css/control_admin/users.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Usuarios</h1>
        </div>
        <div class="option-carrer">
             <button id="#">Agregar Usuario</button>
        </div>
    </div>
    <div class="carrers-container">
        <table class="user-table">
            <thead>
            <tr>
                <th>Unidad de negocio</th>
                <th>Nombre</th>
                <th>Apellido paterno</th>
                <th>Apellido materno</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Lunes</td>
                    <td>Matemáticas</td>
                    <td>8:00 - 9:30</td>
                </tr>
                <tr>
                    <td>Martes</td>
                    <td>Historia</td>
                    <td>10:00 - 11:30</td>
                </tr>
                <tr>
                    <td>Miércoles</td>
                    <td>Programación</td>
                    <td>12:00 - 13:30</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
