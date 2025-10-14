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
        <div class="option-carrer">
            @if(Auth::user()->hasAnyRole(['master']))
             <button id="openCreateCarrer">Agregar Carrera</button>
             @endif
        </div>
        
    </div>
    <!-- Grid de Carreras -->
    <div class="carrers-container">
        
            <div class="carrer-card">
                <div class="carrer-name">
                    <a href="#"><h3 class="carrer-title">$carrers->Title</h3></a>
                </div>
                <div class="line-separator"></div>
                <div class="carrer-card-options">
                    <div class="carrer-info">
                        <span>RVOE: Acuerdo número:</span>
                        <span>$carrers-></span>
                    </div>
                    <div class="carrer-btn-section">
                        {{-- Mostrar Información --}}
                        <button class="btn-info">info</button>
                        {{-- Editar Carrera --}}
                        <button class="btn-edit">edit</button>
                        {{-- Eliminar --}}
                        <form action="" method="" onsubmit="">
                            <button class="btn-delete">del</button>
                        </form>
                    </div>
                </div>
            </div>
        
            <div class="empty-carrers">
                <div class="emty-text">
                    <h2>Sin registro de carreras</h2>
                    <h3>Intentelo mas tarde</h3>
                </div>
            </div>
        
    </div>
</div>
@endsection
