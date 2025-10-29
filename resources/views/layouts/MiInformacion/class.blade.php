<@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
 <div class="container">

    <div class="main-content">
            <div class="content-header">
                <div class="header-top">
                    <div class="titles-container">
                        <div class="page-title">CLASES</div>
                        <div class="period-subtitle">Agosto 2025 – Febrero 2026</div>
                    </div>
                    <div class="welcome-container">
                        <div class="welcome-message">¡Bienvenido(a) Andrea Salmerón!</div>
                        <div class="tasks-section">
                            <div class="tasks-icon">
                                <img src="{{ asset('images/tasks-icon.svg') }}" alt="Icono Tareas">
                            </div>
                            <div class="tasks-text">Tareas</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="classes-container">
                <div class="classes-grid">
                    <div class="class-card">
                        <div class="class-icon-container">
                            <img src="{{ asset('images/negocios-icon.svg') }}" alt="Ícono Negocios">
                        </div>
                        <div class="class-content">
                            <div class="class-title">Inteligencia de Negocios y Big Data</div>
                            <div class="orange-line"></div>
                            <div class="class-footer">
                                <div class="icon-placeholder">+</div>
                                <div class="icon-placeholder">+</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="class-card">
                        <div class="class-icon-container">
                            <img src="{{ asset('images/etica-icon.svg') }}" alt="Ícono Ética">
                        </div>
                        <div class="class-content">
                            <div class="class-title">Etica Profesional</div>
                            <div class="orange-line"></div>
                            <div class="class-footer">
                                <div class="icon-placeholder">+</div>
                                <div class="icon-placeholder">+</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="class-card">
                        <div class="class-icon-container">
                            <img src="{{ asset('images/nube-icon.svg') }}" alt="Ícono Nube">
                        </div>
                        <div class="class-content">
                            <div class="class-title">Computo en la Nube</div>
                            <div class="orange-line"></div>
                            <div class="class-footer">
                                <div class="icon-placeholder">+</div>
                                <div class="icon-placeholder">+</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="class-card">
                        <div class="class-icon-container">
                            <img src="{{ asset('images/forense-icon.svg') }}" alt="Ícono Forense">
                        </div>
                        <div class="class-content">
                            <div class="class-title">Informática Forense</div>
                            <div class="orange-line"></div>
                            <div class="class-footer">
                                <div class="icon-placeholder">+</div>
                                <div class="icon-placeholder">+</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="class-card">
                        <div class="class-icon-container">
                            <img src="{{ asset('images/sustentable-icon.svg') }}" alt="Ícono Sustentable">
                        </div>
                        <div class="class-content">
                            <div class="class-title">Desarrollo<br>Sustentable</div>
                            <div class="orange-line"></div>
                            <div class="class-footer">
                                <div class="icon-placeholder">+</div>
                                <div class="icon-placeholder">+</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="previous-classes">Clases anteriores</div>
        </div>
    </div>
@endsection