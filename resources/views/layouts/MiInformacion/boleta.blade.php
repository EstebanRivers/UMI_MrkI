@extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
 <div class="container">
 <div class="main-content">
            <div class="content-header">
                <div class="header-left">
                    <div class="page-title">BOLETA DE CALIFICACIONES</div>
                </div>
                <div class="header-right">
                    <a href="#" class="icon-action-wrapper" id="icon5-action">
                        <img src="{{ asset('images/icono5.svg') }}" alt="Ícono de notificación" class="icon-top-right">
                    </a>
                    <div class="welcome-message">¡Bienvenido(a) Juan Perez!</div>
                    
                    {{-- INICIO DEL BOTÓN EXPORTAR MODIFICADO --}}
                    <div class="export-button-container">
                        <a href="javascript:void(0);" class="export-button" id="exportButton" title="Exportar Calificaciones">
                            <img src="{{ asset('images/icono6.svg') }}" alt="Exportar" class="export-icon-custom">
                            <span class="export-button-text">Exportar</span>
                        </a>
                    </div>
                    {{-- FIN DEL BOTÓN EXPORTAR MODIFICADO --}}
                </div>
            </div>
            
            <div class="group-1462-container">
                <div class="rectangle-81-button" id="dropdownButton">
                    <img src="{{ asset('images/icono4.svg') }}" alt="Buscar" class="search-icon-custom">
                    
                    <span class="group-1461-text">Buscar materia</span>
                    <span class="dropdown-icon">▼</span>
                </div>
                
                <div class="dropdown-list" id="dropdownList">
                    <div class="dropdown-item">Matemáticas</div>
                    <div class="dropdown-item">Historia</div>
                    <div class="dropdown-item">Ciencias</div>
                    <div class="dropdown-item">Lenguaje</div>
                </div>
            </div>
            
            <div class="content-area">
                <div class="boleta-container">
                    
                    <div class="boleta-header">
                        <div class="column-title column-nombre">Nombre</div>
                        <div class="column-title column-parciales">Parciales</div>
                        <div class="column-title column-calificacion">Calificación Final</div>
                        <div class="column-title column-evaluacion">Evaluación</div>
                        <div class="column-title column-observaciones">Observaciones</div>
                        <div class="column-title column-acciones">Acciones</div>
                    </div>
                    
                    <div class="boleta-content-wrapper">
                        
                        <div class="grades-table-body">
                            <div class="grades-table-row">
                                <div class="grades-table-cell column-nombre">Matemáticas Avanzadas</div>
                                <div class="grades-table-cell column-parciales">80, 85, 90</div>
                                <div class="grades-table-cell column-calificacion">88</div>
                                <div class="grades-table-cell column-evaluacion">Excelente</div>
                                <div class="grades-table-cell column-observaciones">Participación activa</div>
                                
                                <div class="grades-table-cell column-acciones">
                                    <a href="#" class="action-icon-link" title="Ver / Editar">
                                        <img src="{{ asset('images/icono3.svg') }}" alt="Editar">
                                    </a>
                                </div>
                            </div>
                            <div class="grades-table-row">
                                <div class="grades-table-cell column-nombre">Historia Universal I</div>
                                <div class="grades-table-cell column-parciales">75, 70, 80</div>
                                <div class="grades-table-cell column-calificacion">75</div>
                                <div class="grades-table-cell column-evaluacion">Bueno</div>
                                <div class="grades-table-cell column-observaciones">Requiere más lectura</div>
                                
                                <div class="grades-table-cell column-acciones">
                                    <a href="#" class="action-icon-link" title="Ver / Editar">
                                        <img src="{{ asset('images/icono3.svg') }}" alt="Editar">
                                    </a>
                                </div>
                            </div>
                            <div class="grades-table-row">
                                <div class="grades-table-cell column-nombre">Física Cuántica</div>
                                <div class="grades-table-cell column-parciales">95, 92, 98</div>
                                <div class="grades-table-cell column-calificacion">95</div>
                                <div class="grades-table-cell column-evaluacion">Sobresaliente</div>
                                <div class="grades-table-cell column-observaciones">Sin observaciones</div>
                                
                                <div class="grades-table-cell column-acciones">
                                    <a href="#" class="action-icon-link" title="Ver / Editar">
                                        <img src="{{ asset('images/icono3.svg') }}" alt="Editar">
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdownButton = document.getElementById('dropdownButton');
            const dropdownList = document.getElementById('dropdownList');
            const icon5Action = document.getElementById('icon5-action');
            const exportButton = document.getElementById('exportButton'); // Nuevo: Referencia al botón de exportar

            // Lógica para el botón desplegable "buscar materia"
            dropdownButton.addEventListener('click', function(e) {
                e.stopPropagation(); 
                dropdownList.classList.toggle('show');
                this.classList.toggle('active');
            });

            // Lógica para el ícono5 (Notificaciones)
            icon5Action.addEventListener('click', function(e) {
                e.preventDefault(); // Evitar comportamiento de enlace por defecto si hay un href="#"
                console.log('El ícono de notificación ha sido clickeado. Implementar acción futura aquí.');
                // Aquí podrías mostrar un modal de notificaciones, etc.
            });

            // Lógica para el botón de Exportar (Nuevo)
            exportButton.addEventListener('click', function(e) {
                e.preventDefault(); // Evitar comportamiento de enlace por defecto (si href="javascript:void(0);")
                console.log('El botón Exportar ha sido clickeado. Aquí puedes iniciar la lógica de exportación.');
                // Ejemplo: Podrías llamar a una función para generar un archivo CSV o PDF
                alert('Iniciando exportación de calificaciones...');
            });


            // Ocultar el listado si se hace click fuera del botón desplegable
            document.addEventListener('click', function(event) {
                if (!dropdownButton.contains(event.target) && !dropdownList.contains(event.target)) {
                    dropdownList.classList.remove('show');
                    dropdownButton.classList.remove('active');
                }
            });
        });
    </script>


    @endsection