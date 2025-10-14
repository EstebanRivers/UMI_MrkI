@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

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
        
    </div>
</div>
@endsection
