@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/control_admin/base.css', 'resources/js/app.js'])

@section('content')
<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Carreras</h1>
        </div>
        <div class="option-carrer">
             <button id="openCreateCarrer">Agregar Carrera</button>
        </div>
        @include('layouts.ControlAdmin.carrers.create')
    </div>
    <div class="carrers-container">
        @if (empty($carrers))
        <div class="empty-carrers">
            <div class="emty-text">
                <h2>Sin registro de carreras</h2>
                <h3>Intentelo mas tarde</h3>
            </div>
        </div>
        @else

        @endif
    </div>
</div>
@endsection
