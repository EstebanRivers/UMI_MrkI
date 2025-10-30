@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')

<div class="main-content">
            <div class="welcome-container">
                <center>
                    <img src="{{ asset('images/LOGO1.png') }}" alt="Imagen de bienvenida" class="welcome-image">
                </center>
                <div class="welcome-message">
                    ¡Bienvenido(a) Usuario Master!
                </div>
            </div>
        </div>
        @endsection