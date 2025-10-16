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
                <button type="button" onclick="openModal('createFormModal')">Agregar Carrera</button>
            @endif
        </div>
        <div id="createFormModal" class="custom-modal">
            <span class="close-button" onclick="closeModal('createFormModal')">&times;</span>
            {{-- INCLUYENDO EL MÓDULO DEL FORMULARIO AQUÍ --}}
            @include('layouts.ControlAdmin.Carreras.create')
        </div>
    </div>
    <!-- Grid de Carreras -->
    <div class="carrers-container">
        @forelse ($careers as $carrera)
            <div class="carrer-card">
                <div class="carrer-name">
                    <a href="#"><h3 class="carrer-title">{{ $carrera->name }}</h3></a>
                </div>
                <div class="line-separator"></div>
                <div class="carrer-card-options">
                    <div class="carrer-info">
                        <span>RVOE: Acuerdo número:</span>
                        <span>{{ $carrera->official_id }}</span>
                    </div>
                    <div class="carrer-btn-section">
                        {{-- Mostrar Información --}}
                        <button class="btn-info">info</button>
                        {{-- Editar Carrera --}}
                        <button class="btn-edit">edit</button>
                        {{-- Eliminar --}}
                        <form action="{{ route('career.destroy', $carrera->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta carrera?');">
                            <button class="btn-delete">del</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-carrers">
                <div class="emty-text">
                    <h2>Sin registro de carreras</h2>
                    <h3>Intentelo mas tarde</h3>
                </div>
            </div>
        @endforelse
    </div>
</div>
{{-- Script JS para abrir/cerrar (el mismo de antes) --}}
<script>
    function openModal(modalId) {
        document.getElementById(modalId).style.display = "block";
    }
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = "none";
    }
    window.onclick = function(event) {
        const modal = document.getElementById('createFormModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
@endsection
