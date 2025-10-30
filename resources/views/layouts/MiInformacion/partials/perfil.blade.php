@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
    <div class="container">
            
        
        
        <div class="main-content">
            <div class="content-header">
                <div class="page-title">PERFIL</div>
                <div class="welcome-message">¡Bienvenido(a) Andrea Salmerón!</div>
            </div>
            
            <div class="welcome-container">
                <img src="{{ asset('images/LOGO11.svg') }}" class="welcome-image" alt="Logo perfil">
                <div class="orange-line"></div>
                <div class="student-name">Andrea Lisset Salmerón Cárdenas</div>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <span class="info-title">Carrera:</span>
                    <span class="info-content">Licenciatura en Sistemas y Seguridad Informática</span>
                </div>
                
                <div class="inline-group">
                    <div class="inline-pair">
                        <span class="info-title">Matrícula:</span>
                        <span class="info-content">XXXXXXX</span>
                    </div>
                    <div class="inline-pair">
                        <span class="info-title">Semestre:</span><span class="info-content">8</span>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="inline-group">
                    <div class="inline-pair">
                        <span class="info-title">Correo:</span>
                        <span class="info-content">prueba123@UHTA.com</span>
                    </div>
                    <div class="tight-group">
                        <div class="tight-pair">
                            <span class="info-title">Teléfono:</span><span class="info-content">1234567896</span>
                        </div>
                        <div class="tight-pair">
                            <span class="info-title">Edad:</span><span class="info-content">24 años</span>
                        </div>
                    </div>
                </div>
                
                <div class="info-row">
                    <span class="info-title">CURP:</span>
                    <span class="info-content">XXXXXXXXXXX</span>
                </div>
                
                <div class="info-row">
                    <span class="info-title">Fecha de nacimiento:</span>
                    <span class="info-content">XXXXXXXXXXX</span>
                </div>
                
                <div class="info-row">
                    <span class="info-title">Dirección:</span>
                </div>
                
                <div class="info-row">
                    <span class="info-title">Colonia:</span>
                    <span class="info-content">XXXXXXXXXXXXXXX</span>
                </div>
                
                <div class="info-row">
                    <span class="info-title">Calle:</span>
                    <span class="info-content">########################</span>
                </div>
                
                <div class="info-row">
                    <span class="info-title">Ciudad:</span>
                    <span class="info-content">Acapulco de Juárez</span>
                </div>
                
                <div class="inline-group">
                    <div class="inline-pair">
                        <span class="info-title">Estado:</span>
                        <span class="info-content">Guerrero</span>
                    </div>
                    <div class="inline-pair">
                        <span class="info-title">C.P.:</span><span class="info-content">000000</span>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection