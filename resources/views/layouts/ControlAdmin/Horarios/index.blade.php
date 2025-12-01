@extends('layouts.app')

@section('title', 'Control Administrativo - ' . session('active_institution_name'))

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
@php
    // Define la variable de control para toda la plantilla
    // Si la variable $horario existe (porque estamos en la ruta de edición), es TRUE.
    $modoEdicion = isset($horario); 
@endphp

<div class ="container">
    <div class ="content-header">
        <div class="content-title">
            <h1>Horarios</h1>
        </div>
    </div>
    <div class = "creator-container">
        <div class = "schedule-lists">
            <form id="schedule_form" method="POST" action="{{ $modoEdicion ? route('control.schedules.update', $horario->id) : route('control.schedules.store') }}">
                @csrf
                @if ($modoEdicion)
                    @method('PUT') 
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h4>🚨 Se encontraron errores:</h4>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @error('franjas_json')
                    <div class="alert alert-warning">{{ $message }}</div>
                @enderror
                <div class = "schedule-list-select">
                    <label for = "career_select">Carrera:</label>
                    <select id="carrera_select" name="carrera_id">
                        @if ($modoEdicion) disabled @endif
                        <option value="">Seleccione una Carrera</option>
                        {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                        @foreach ($carreras as $carrera)
                            <option value = "{{$carrera->id}}" @if ($modoEdicion && $carrera->id == $horario->carrera_id) selected @endif>{{$carrera->name}}</option>
                        @endforeach
                    </select>
                    @if ($modoEdicion)
                        <input type="hidden" name="carrera_id" value="{{ $horario->carrera_id }}">
                    @endif
                </div>
                <div class = "schedule-list-select">
                    <label for = "materia_select">Materia</label>
                    <select id="materia_select" name="materia_id">
                        @if ($modoEdicion) disabled @endif
                        <option value="">Seleccione una Materia</option>
                        {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                        @if ($modoEdicion) disabled @endif
                        @foreach ($materias as $materia)
                            <option value = "{{$materia->id}}" @if ($modoEdicion && $materia->id == $horario->materia_id) selected @endif>{{$materia->nombre}}</option>
                        @endforeach
                    </select>
                    @if ($modoEdicion)
                        <input type="hidden" name="materia_id" value="{{ $horario->materia_id }}">
                    @endif
                </div>
                <div class = "schedule-list-select">
                    <label for = "docente_select">Docente</label>
                    <select id="docente_select" name="docente_id">
                        <option value="">Seleccione una Docente</option>
                        {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                        @foreach ($docentes as $docente)
                            <option value = "{{$docente->id}}" @if ($modoEdicion && $docente->id == $horario->user_id) selected @endif>{{$docente->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <h3>Seleccione los Horarios</h3>
                <div class="schedule-settings">
                    {{-- Div para Botones para seleccionar los dias (Lunes a Domingo) --}}
                    <div class="day-selection-buttons">
                        <button type="button" data-day="1">Lun</button>
                        <button type="button" data-day="2">Mar</button>
                        <button type="button" data-day="3">Mié</button>
                        <button type="button" data-day="4">Jue</button>
                        <button type="button" data-day="5">Vie</button>
                        <button type="button" data-day="6">Sáb</button>
                        <button type="button" data-day="7">Dom</button>
                    </div>
                    
                    {{-- Div para 2 input (Hora Inicio y Hora Fin) --}}
                    <div class="time-inputs">
                        <input type="time" id="hora_inicio" name="hora_inicio" required>

                        <input type="time" id="hora_fin" name="hora_fin" required>
                    </div>
                    
                    {{-- Boton redondo para confirmar la seleccion de horas/días y añadirla al resumen --}}
                    <button type="button" class="add-time-slot-btn">
                        <i class="fas fa-plus"></i>svg
                    </button>
                    
                </div>
                <div class="schedule-resume">
                    <h3>Vista Previa</h3>
                    {{-- Aquí se observarán en una tabla las franjas añadidas --}}
                    <table id="time-slots-table">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="time_slots_body">
                            {{-- Filas de franjas horarias se insertarán con JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <div class = "schedule-list-select">
                    <label for = "aula_select">Aula</label>
                    <select id="aula_select" name="aula_id">
                        <option value="">Seleccione una Carrera</option>
                    {{-- Aquí va el loop para cargar las carreras desde la BD --}}
                    @foreach ($aulas as $aula)
                        <option value = "{{$aula->id}}" @if ($modoEdicion && $aula->id == $horario->aula_id) selected @endif>{{$aula->numero_aula}}</option>
                    @endforeach
                    
                    </select>
                </div>
                <div class = "schedule-submit">
                    <button type="submit" id="save_schedule_btn" class="submit-button">Submit</button>
                </div>
            </form>
        </div>
        <div class = "schedule-table">
            <div class="toolbar__search">
                <form action="{{ route('control.schedules.index') }}" method="GET" id="search-form" class="d-flex mb-4">
                    <input type="text" 
                        name="search_query" 
                        id="search-input" 
                        class="form-control me-2" 
                        placeholder="Buscar por..."
                        value="{{ request('search_query') }}"
                        autocomplete="off" {{-- Recomendado para búsquedas en tiempo real --}}>
                </form>
            </div>
            <div class="Table-view">
                <table class="tabla-base tabla-rayas tabla-bordes">
                    <theader class="encabezado-tabla">
                        <tr>
                                <th>Carrera</th>
                                <th>Materia</th>
                                <th>Docentes</th>
                                <th>Acciones</th>
                        </tr>
                    </theader>
                    <tbody class = "cuerpo-tabla">
                        @forelse ($horarios as $horario)
                            <tr>
                                {{-- Acceder a las relaciones cargadas con with() --}}
                                <td>{{ $horario->carrera->name }}</td>
                                <td>{{ $horario->materia->nombre }}</td>
                                <td>{{ $horario->user->nombre }}</td>
                                <td>
                                    <form action="{{ route('horarios.edit', $horario->id) }}" method="GET" style="display:inline;">
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <svg width="20" height="20" viewBox="0 0 24 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                d="M22.3364 0.648281C21.2991 -0.216094 19.6225 -0.216094 18.5852 0.648281L17.1596 1.83235L21.7964 5.69638L23.2221 4.50836C24.2593 3.64399 24.2593 2.24678 23.2221 1.38241L22.3364 0.648281ZM8.16538 9.33149C7.87646 9.57225 7.65386 9.86827 7.52598 10.1959L6.12403 13.7007C5.98668 14.0402 6.09561 14.4151 6.39874 14.6717C6.70186 14.9282 7.15181 15.015 7.56387 14.9006L11.7697 13.7323C12.1581 13.6257 12.5133 13.4402 12.8069 13.1995L20.7308 6.59233L16.0892 2.72436L8.16538 9.33149ZM4.54685 2.31783C2.03661 2.31783 0 4.015 0 6.10686V16.211C0 18.3028 2.03661 20 4.54685 20H16.6718C19.182 20 21.2186 18.3028 21.2186 16.211V12.4219C21.2186 11.7233 20.5413 11.1589 19.703 11.1589C18.8647 11.1589 18.1874 11.7233 18.1874 12.4219V16.211C18.1874 16.9096 17.5101 17.474 16.6718 17.474H4.54685C3.70852 17.474 3.03123 16.9096 3.03123 16.211V6.10686C3.03123 5.40826 3.70852 4.84385 4.54685 4.84385H9.09369C9.93202 4.84385 10.6093 4.27944 10.6093 3.58084C10.6093 2.88223 9.93202 2.31783 9.09369 2.31783H4.54685Z"
                                                fill="black" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('horarios.destroy', $horario->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        {{-- Laravel necesita el método @method('DELETE') para simular la petición DELETE --}}
                                        @method('DELETE') 
                                        
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Está seguro de eliminar este horario? Esta acción es irreversible.');">
                                            <svg width="20" height="20" viewBox="0 0 27 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                d="M27 3C27 1.34531 25.6547 0 24 0H9.62344C8.82656 0 8.0625 0.314062 7.5 0.876562L0.440625 7.94062C0.159375 8.22187 0 8.60156 0 9C0 9.39844 0.159375 9.77813 0.440625 10.0594L7.5 17.1234C8.0625 17.6859 8.82656 18 9.62344 18H24C25.6547 18 27 16.6547 27 15V3ZM12.7031 5.20312C13.1438 4.7625 13.8562 4.7625 14.2922 5.20312L16.4953 7.40625L18.6984 5.20312C19.1391 4.7625 19.8516 4.7625 20.2875 5.20312C20.7234 5.64375 20.7281 6.35625 20.2875 6.79219L18.0844 8.99531L20.2875 11.1984C20.7281 11.6391 20.7281 12.3516 20.2875 12.7875C19.8469 13.2234 19.1344 13.2281 18.6984 12.7875L16.4953 10.5844L14.2922 12.7875C13.8516 13.2281 13.1391 13.2281 12.7031 12.7875C12.2672 12.3469 12.2625 11.6344 12.7031 11.1984L14.9062 8.99531L12.7031 6.79219C12.2625 6.35156 12.2625 5.63906 12.7031 5.20312Z"
                                                fill="#D30303" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            {{-- 💡 Este bloque se ejecuta cuando $horarios está vacío --}}
                            <tr>
                                <td colspan="6" class="text-center">
                                    No se encontraron horarios que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // 1. INICIALIZACIÓN DE LA DATA TEMPORAL
    // 💡 Cargar el JSON solo si estamos en modo edición
    const modoEdicion = {{ $modoEdicion ? 'true' : 'false' }};
    const franjasDataPHP = {!! $modoEdicion ? json_encode($horario->franjas->toArray()) : '[]' !!}; // Cargar el JSON si estamos editando// Este array guardará todas las franjas horarias añadidas por el usuario.
    
    let franjasTemporales = [];
    let tempIdCounter = 1; // Contador para dar un ID único temporal a cada franja

    // 2. Llenar el array temporal con los datos existentes
    if (modoEdicion && franjasDataPHP.length > 0) {
        
        // 1. Objeto temporal para agrupar por hora
        const gruposPorHora = {};

        // 2. Iterar sobre la data de la BD y agrupar por clave única (hora inicio + hora fin)
        franjasDataPHP.forEach(franja => {
            
            // 🚨 La clave de la agrupación es la combinación de las horas 🚨
            const claveAgrupacion = franja.hora_inicio + '|' + franja.hora_fin;
            
            if (!gruposPorHora[claveAgrupacion]) {
                // Si el grupo no existe, lo inicializamos
                gruposPorHora[claveAgrupacion] = {
                    dias_semana: [], // Array de días que comparten este horario
                    hora_inicio: franja.hora_inicio,
                    hora_fin: franja.hora_fin
                };
            }
            
            // Agregamos el día al grupo existente
            gruposPorHora[claveAgrupacion].dias_semana.push(franja.dias_semana);
        });

        // 3. Convertir el objeto agrupado de nuevo al array final (franjasTemporales)
        for (const clave in gruposPorHora) {
            const grupo = gruposPorHora[clave];
            franjasTemporales.push({
                temp_id: tempIdCounter++, 
                dias_semana: grupo.dias_semana, // Esto es ahora un array de números de día (ej: [1, 2, 3])
                hora_inicio: grupo.hora_inicio.substring(0, 5), 
                hora_fin: grupo.hora_fin.substring(0, 5), 
            });
        }

        tempIdCounter = franjasTemporales.length + 1;

        // 4. Dibujar la tabla
        document.addEventListener('DOMContentLoaded', function() {
            actualizarTablaResumen(); 
        });
    }
    // ---------------------------------------------------------------------
    // FUNCIONES DE UTILIDAD
    // ---------------------------------------------------------------------
    
    /**
     * Función para convertir números de día (1=Lunes) a nombres (Lunes).
     */
    const getNombreDia = (numeroDia) => {
        const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return dias[numeroDia - 1] || 'Día Inválido';
    };

    /**
     * Función que limpia la selección de días y los inputs de hora.
     */
    const limpiarFormularioTiempo = () => {
        document.getElementById('hora_inicio').value = '';
        document.getElementById('hora_fin').value = '';
        // Deseleccionar botones
        document.querySelectorAll('.day-selection-buttons button.selected').forEach(btn => {
            btn.classList.remove('selected');
        });
    };
    
    /**
     * Función principal para actualizar la tabla de resumen (schedule-resume).
     */
    const actualizarTablaResumen = () => {
        const tbody = document.getElementById('time_slots_body');
        
        // 1. Limpiar el contenido anterior (la respuesta a nuestra pregunta)
        tbody.innerHTML = ''; 

        // 2. Iterar sobre el array temporal y crear una fila por cada franja
        franjasTemporales.forEach(franja => {
            const tr = document.createElement('tr');
            
            // Convertir el array de números [1, 2] a una cadena legible "Lunes, Martes"
            const diaNombres = franja.dias_semana.map(getNombreDia).join(', ');
            
            // Usamos substring para mostrar solo HH:MM sin los segundos
            const horaInicioVisible = franja.hora_inicio.substring(0, 5);
            const horaFinVisible = franja.hora_fin.substring(0, 5);

            tr.innerHTML = `
                <td>${diaNombres}</td>
                <td>${horaInicioVisible}</td>
                <td>${horaFinVisible}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger delete-franja" data-id="${franja.temp_id}">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    };

    // ---------------------------------------------------------------------
    // EVENT LISTENERS (La Lógica del Usuario)
    // ---------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function() {
        
        // Lógica para togglear la clase 'selected' en los botones de día
        document.querySelectorAll('.day-selection-buttons button').forEach(button => {
            button.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });
        
        // 1. Escuchar el botón "Añadir Franja"
        const addButton = document.querySelector('.add-time-slot-btn');
        addButton.addEventListener('click', function() {
            const diasSeleccionados = [];
            // Recorre los botones seleccionados para obtener los números de día
            document.querySelectorAll('.day-selection-buttons button.selected').forEach(button => {
                const diaNum = parseInt(button.getAttribute('data-day'));
                diasSeleccionados.push(diaNum);
            });

            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFin = document.getElementById('hora_fin').value;
            
            if (diasSeleccionados.length === 0 || !horaInicio || !horaFin) {
                alert("Por favor, selecciona al menos un día y las horas de inicio/fin.");
                return; 
            }

            // Crear el objeto de la nueva franja
            const nuevaFranja = {
                temp_id: tempIdCounter++,
                dias_semana: diasSeleccionados, // [1, 2]
                hora_inicio: horaInicio + ":00", // "08:00:00"
                hora_fin: horaFin + ":00"
            };

            // Añadir al array, actualizar la vista y limpiar el formulario
            franjasTemporales.push(nuevaFranja);
            actualizarTablaResumen(); 
            limpiarFormularioTiempo();
        });

        // 2. Escuchar el botón "Eliminar" desde la tabla resumen
        document.getElementById('time_slots_body').addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-franja')) {
                const tempIdToDelete = parseInt(e.target.getAttribute('data-id'));
                
                // Filtramos el array para eliminar la franja que coincida con el ID temporal
                franjasTemporales = franjasTemporales.filter(f => f.temp_id !== tempIdToDelete);
                
                actualizarTablaResumen();
            }
        });

        // 3. 🚨 LÓGICA DE GUARDADO FINAL 🚨
        // Cuando el usuario presione el botón "Guardar Horario Completo",
        // debemos añadir el array franjasTemporales como un campo oculto al formulario
        // para que Laravel lo reciba. Esto es crucial para el Controller.
        document.getElementById('save_schedule_btn').addEventListener('click', function(e) {
            
            e.preventDefault(); 
            
            // 💡 CORRECCIÓN: Usamos getElementById o el selector #
            const scheduleForm = document.getElementById('schedule_form');
            
            // **Añadir comprobación de seguridad:** Si no encuentra el formulario, detente y notifica.
            if (!scheduleForm) {
                console.error("Error: No se encontró el formulario. Asegúrate de que el <form> tenga id='schedule_form'.");
                return; 
            }
            
            // 💡 OPTIMIZACIÓN: Revisar si el input oculto ya existe para no añadirlo varias veces
            let hiddenInput = scheduleForm.querySelector('input[name="franjas_json"]');

            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'franjas_json'; 
                scheduleForm.appendChild(hiddenInput);
            }
            
            // Convertir el array JS a una cadena JSON y asignarlo
            hiddenInput.value = JSON.stringify(franjasTemporales); 
            
            // Enviar el formulario
            scheduleForm.submit();
        });
        
    });
    // =========================================================
    // 2. LÓGICA DE BÚSQUEDA EN TIEMPO REAL
    // =========================================================
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        let searchTimeout; // Variable para controlar el tiempo de espera
        
        if (searchInput && searchForm) {
            
            searchInput.addEventListener('blur', function() {
                if (searchInput.value.trim().length === 0) {
                    searchForm.submit();
                }
            });
            // 💡 1. Escuchar el evento 'input' (se dispara en cada tecla)
            searchInput.addEventListener('input', function() {
                
                // Limpiar el temporizador anterior para evitar envíos múltiples
                clearTimeout(searchTimeout);

                const query = searchInput.value.trim();

                // 2. Lógica de Limpieza Instantánea (Si el campo se vacía)
                // Enviamos el formulario inmediatamente si el campo está vacío.
                if (query.length === 0) {
                    searchForm.submit();
                    return; 
                }

                // 3. Lógica de Debouncing (Si hay texto)
                // Solo enviamos el formulario si la consulta tiene al menos 2 caracteres
                // Y si el usuario deja de escribir por 300ms.
                if (query.length >= 2) {
                    searchTimeout = setTimeout(function() {
                        searchForm.submit();
                    }, 300); // Espera de 300 milisegundos
                }
            });
        }
    });
</script>
@endpush
@endsection
