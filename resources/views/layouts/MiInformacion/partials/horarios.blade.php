 @extends('layouts.app')

@section('title', 'Mi Información - ' . session('active_institution_name'))

@section('content')
 
 
 <div class="container">
            <div class="main-content">
            <div class="content-header">
                <div class="header-top">
                    <div class="titles-container">
                        <div class="page-title">HORARIOS</div>
                        <div class="period-subtitle">Agosto 2025 – Febrero 2026</div>
                    </div>
                    <div class="welcome-container">
                        <div class="welcome-message">¡Bienvenido(a) Andrea Salmerón!</div>
                    </div>
                </div>
            </div>
            
            <div class="schedule-wrapper">
                <div class="schedule-container">
                    <div class="schedule-header">
                        <div class="time-header">HORAS</div>
                        <div class="day-header">Lunes</div>
                        <div class="day-header">Martes</div>
                        <div class="day-header">Miércoles</div>
                        <div class="day-header">Jueves</div>
                        <div class="day-header">Viernes</div>
                        <div class="day-header">Sábado</div>
                        <div class="day-header">Domingo</div>
                    </div>
                    
                    <div class="schedule-scroll-container">
                        <div class="schedule-body">
                            <div class="time-labels-column">
                                <div class="time-label">07:00<br>08:00</div>
                                <div class="time-label">08:00<br>09:00</div>
                                <div class="time-label">09:00<br>10:00</div>
                                <div class="time-label">10:00<br>11:00</div>
                                <div class="time-label">11:00<br>12:00</div>
                                <div class="time-label">12:00<br>13:00</div>
                                <div class="time-label">13:00<br>14:00</div>
                                <div class="time-label">14:00<br>15:00</div>
                                <div class="time-label">15:00<br>16:00</div>
                                <div class="time-label">16:00<br>17:00</div>
                                <div class="time-label">17:00<br>18:00</div>
                                <div class="time-label">18:00<br>19:00</div>
                                <div class="time-label">19:00<br>20:00</div>
                                <div class="time-label">20:00<br>21:00</div>
                            </div>
                            
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Ética Profesional</div>
                                    <div class="class-room">Sala 302</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Ética Profesional</div>
                                    <div class="class-room">Sala 302</div>
                                </div>
                            </div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Inteligencia de Negocios</div>
                                    <div class="class-room">Lab. Computación</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Inteligencia de Negocios</div>
                                    <div class="class-room">Lab. Computación</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Cómputo en la Nube</div>
                                    <div class="class-room">Sala 405</div>
                                </div>
                            </div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Informática Forense</div>
                                    <div class="class-room">Lab. Tecnología</div>
                                </div>
                            </div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Cómputo en la Nube</div>
                                    <div class="class-room">Sala 405</div>
                                </div>
                            </div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Informática Forense</div>
                                    <div class="class-room">Lab. Tecnología</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Desarrollo Sustentable</div>
                                    <div class="class-room">Aula Magna</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Desarrollo Sustentable</div>
                                    <div class="class-room">Aula Magna</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Big Data</div>
                                    <div class="class-room">Lab. Computación</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Big Data</div>
                                    <div class="class-room">Lab. Computación</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Taller Investigación</div>
                                    <div class="class-room">Sala 204</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Taller Investigación</div>
                                    <div class="class-room">Sala 204</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Seminario TI</div>
                                    <div class="class-room">Sala 105</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Redes Avanzadas</div>
                                    <div class="class-room">Lab. Redes</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Redes Avanzadas</div>
                                    <div class="class-room">Lab. Redes</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Seguridad Informática</div>
                                    <div class="class-room">Sala 303</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Seguridad Informática</div>
                                    <div class="class-room">Sala 303</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Programación Móvil</div>
                                    <div class="class-room">Lab. Desarrollo</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Programación Móvil</div>
                                    <div class="class-room">Lab. Desarrollo</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Inglés Técnico</div>
                                    <div class="class-room">Sala 201</div>
                                </div>
                            </div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Tutorías</div>
                                    <div class="class-room">Sala 101</div>
                                </div>
                            </div>
                            
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell"><div class="empty-cell"></div></div>
                            <div class="schedule-cell">
                                <div class="class-item">
                                    <div class="class-name">Laboratorio</div>
                                    <div class="class-room">Lab. Central</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="export-button" onclick="exportData()">Exportar</button>
        </div>
    </div>

    <script>
        function exportData() {
            alert('Función de exportación activada. Esta acción puede conectarse a un sistema de exportación real.');
        }
    </script>
    
    @endsection