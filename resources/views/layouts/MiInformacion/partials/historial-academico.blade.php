 @extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
 
 
 <div class="container">
    <div class="main-content">
            <div class="content-header">
                <div class="header-top">
                    <div class="titles-container">
                        <div class="page-title">HISTORIAL ACADÉMICO</div>
                    </div>
                    <div class="welcome-container">
                        <div class="welcome-message">¡Bienvenido(a) Andrea Salmerón!</div>
                        <div class="action-buttons">
                            <button class="action-button" onclick="showReticula()">Reticula escolar</button>
                            <button class="action-button" onclick="showBoleta()">Boleta de calificaciones</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="student-info">
                    <div class="info-item">
                        <span class="info-label">No. de matrícula:</span>
                        <span class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Semestre actual:</span>
                        <span class="info-value"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Carrera:</span>
                        <span class="info-value"></span>
                    </div>
                </div>
                <div class="specialty-container">
                    <div class="specialty-label">Especialidad:</div>
                    <div class="specialty-value"></div>
                </div>
            </div>

            <div class="tabla-integrada">
                <div class="table-header">
                    <div class="header-item header-semestre">Semestre</div>
                    <div class="header-item header-materias">Materias</div>
                    <div class="header-item header-creditos">Creditos</div>
                    <div class="header-item header-calificacion">Calificación</div>
                    <div class="header-item header-evaluacion">Evaluación</div>
                    <div class="header-item header-observaciones">Observaciones</div>
                </div>

                <div class="materias-container">
                    <div class="rectangle-container">
                        <div class="rectangle-760">
                            
                            <div class="rectangle-details">
                                <div class="rectangle-number"></div>
                                <div class="number-line"></div>
                                <div class="period-container">
                                    <div class="rectangle-period"> <br></div>
                                    <div class="rectangle-line"></div>
                                </div>
                                
                                <div class="semester-grade-container">
                                    <div class="rectangle-grade"><br></div>
                                    <div class="grade-value"></div>
                                </div>
                            </div>
                            
                            <div class="rectangle-dividers">
                                <div class="semestre-divider"></div> 
                                <div class="materia-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="creditos-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="calificacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="evaluacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="observaciones-divider-spacer"></div>
                            </div>

                            <div class="materias-content">
                                </div>
                        </div>

                        <div class="rectangle-760">
                            <div class="rectangle-details">
                                <div class="rectangle-number"></div>
                                <div class="number-line"></div>
                                <div class="period-container">
                                    <div class="rectangle-period"><br></div>
                                    <div class="rectangle-line"></div>
                                </div>
                                <div class="semester-grade-container">
                                    <div class="rectangle-grade"><br>:</div>
                                    <div class="grade-value"></div>
                                </div>
                            </div>
                            <div class="rectangle-dividers">
                                <div class="semestre-divider"></div> 
                                <div class="materia-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="creditos-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="calificacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="evaluacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="observaciones-divider-spacer"></div>
                            </div>
                            <div class="materias-content">
                                </div>
                        </div>
                        
                        <div class="rectangle-760">
                            <div class="rectangle-details">
                                <div class="rectangle-number"></div>
                                <div class="number-line"></div>
                                <div class="period-container">
                                    <div class="rectangle-period"><br></div>
                                    <div class="rectangle-line"></div>
                                </div>
                                <div class="semester-grade-container">
                                    <div class="rectangle-grade"><br></div>
                                    <div class="grade-value"></div>
                                </div>
                            </div>
                            <div class="rectangle-dividers">
                                <div class="semestre-divider"></div> 
                                <div class="materia-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="creditos-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="calificacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="evaluacion-divider-spacer"></div>
                                <div class="vertical-divider"></div>
                                <div class="observaciones-divider-spacer"></div>
                            </div>
                            <div class="materias-content">
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="calificacion-final">Calificación final</div>

            <div class="creditos-buttons">
                <div class="credito-button">
                    <span>Créditos totales:</span>
                    <span class="credito-valor">250</span>
                </div>
                <div class="credito-button">
                    <span>Créditos acumulados:</span>
                    <span class="credito-valor">180</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showReticula() { alert('Mostrando retícula escolar. Esta función puede abrir a modal o redirigir a otra página.'); }
        function showBoleta() { alert('Mostrando boleta de calificaciones. Esta función puede abrir a modal o redirigir a otra página.'); }
        document.addEventListener('DOMContentLoaded', function() {
            const materiasContainer = document.querySelector('.materias-container');
            if (materiasContainer) { materiasContainer.style.overflowY = 'scroll'; }
        });
    </script>
    @endsection
