@extends('layouts.app')

@section('title', 'Añadir Temas a ' . $course->title)

@vite(['resources/css/topic.css', 'resources/js/app.js'])

@section('content')
<div class="topics-container">

    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Encabezado --}}
    <div class="topics-header">
        <div>
            <h1>Añadir Temas y Actividades</h1>
            <h2>Curso: {{ $course->title }}</h2>
        </div>
        <a href="{{ route('courses.index') }}" class="btn-secondary">
            Finalizar
        </a>
    </div>

    <div class="topics-layout">
        {{-- Columna del formulario --}}
        <div class="topics-form">
            @if ($errors->any())
                <div class="alert-danger">
                    <strong>¡Ups! Hubo algunos problemas:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div id="form-topic" class="form-mode-container" style="display: block;">
                <div class="header-topic" style="display:flex; justify-content: space-between;">
                <h3>Añadir Nuevo Tema</h3>
                </div>
                <form id="topic-form" action="{{ route('topics.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                    {{-- Título --}}
                    <div class="form-group">
                        <label for="title">Título del Tema</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label for="description">Descripción Detallada del Tema</label>
                        <textarea id="description" name="description" rows="5"></textarea>
                    </div>

                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="file">Adjuntar Archivo (PDF, Word, PPT o Video)</label>
                        <input type="file" id="file" name="file">
                    </div>
                     <button type="submit" class="btn-success">+ Añadir Tema </button>

                </form>
            </div>

            {{-- 2.2 FORMULARIO DE SUBTEMA (Inicialmente oculto) --}}
            <div id="form-subtopic" class="form-mode-container" style="display: none;">
                {{-- Encabezado con el contexto del padre --}}
                <div class="header-topic">
                    <h3 id="subtopic-form-title">Añadir Nuevo Subtema</h3>
                    <p id="subtopic-context" style="color: #007bff; font-weight: bold;"></p>
                </div>
                <form id="subtopic-form" action="{{$formActions}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    {{-- Necesitaremos JS para establecer esta ruta y el topic_id --}}
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <input type="hidden" name="topic_id" id="subtopic-topic-id"> 

                    {{-- Campos Subtema (simples) --}}
                    <div class="form-group">
                        <label for="subtopic-title">Título del Subtema</label>
                        <input type="text" id="subtopic-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="subtopic-description">Descripción Detallada</label>
                        <textarea id="subtopic-description" name="description" rows="5"></textarea>
                    </div>
                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="subtopic-file">Adjuntar Archivo (PDF, Word, PPT o Video)</label>
                        <input type="file" id="subtopic-file" name="file">
                    </div>
                    <button type="submit" class="btn-success">+ Añadir Subtema </button>
                </form>
            </div>

            {{-- 2.3 FORMULARIO DE ACTIVIDAD (Inicialmente oculto) --}}
            <div id="form-activity" class="form-mode-container" style="display: none;">
                <h3>Añadir Nueva Actividad</h3>
                <form id="activity-form" action="{{route('activities.store')}}" method="POST">
                    @csrf
                    <input type="hidden" name="subtopic_id" id="activity-subtopic-id">
                    <input type="hidden" name="topic_id" id="activity-topic-id">

                        <div class="header-activity" style="display:flex; justify-content: space-between; margin-bottom: 10px;">
                            <h5>Nueva Actividad</h5>
                            <button type="submit" class="btn-primary">+ Añadir Actividad</button>
                        </div>
                        
                        <div class="form-group">
                            <input type="text" name="title" placeholder="Título de la actividad" required>
                        </div>

                        <div class="form-group">
                            <select name="type" required class="activity-type-selector">
                                <option value="" disabled selected>Selecciona el tipo...</option>
                                <option value="Cuestionario">Cuestionario</option>
                                <option value="SopaDeLetras">Sopa de Letras</option>
                            </select>
                        </div>

                        <div class="activity-fields-container">
                            <div class="activity-fields" id="fields-Cuestionario">
                                <div class="form-group">
                                    <label>Pregunta del cuestionario:</label>
                                    <input type="text" name="content[question]" class="form-field-cuestionario" placeholder="Escribe la pregunta aquí">
                                </div>
                                <div>
                                    <label>Opciones de respuesta (marca la correcta):</label>
                                    @for ($i = 0; $i < 4; $i++)
                                        <div class="quiz-option">
                                            <input type="radio" name="content[correct_answer]" value="{{ $i }}">
                                            <input type="text" name="content[options][]" class="form-field-cuestionario" placeholder="Opción {{ $i + 1 }}">
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                </form>
            </div>            
        </div>

        {{-- Columna lista de temas --}}                
        <div class="topics-list">
            <div class="topics-list-header">
                <div style="margin-bottom: 5px;">
                    <h3>Temas del Curso ({{ $course->topics->count() }})</h3>
                    <p id="selection-context" style="font-size: 0.9em; color: #555; min-height: 1.2em;"></p>
                </div>
                <div class="content-btn" style="display: flex; gap: 8px; margin-bottom: 10px;">
                        <button id="mode-topic" class="btn-topic" data-mode="topic">+ Añadir Tema </button>
                        <button id="mode-subtopic" class="btn-subtopic" data-mode="subtopic" disabled>+ Añadir Subtema </button>
                        <button id="mode-activity" class="btn-activities" data-mode="activity" disabled>+ Añadir Actividad </button>
                </div>
            </div>
                
            {{-- Lista de temas y subtemas --}}
            <div class="topics-list-content">
                @forelse ($course->topics as $topic)
                <div class="topic-card" data-topic-id="{{ $topic->id }}" data-topic-title="{{ $topic->title }}">
                    <div class="card-body">

                        {{-- Cabecera del tema --}}
                        <div class="topic-header">
                            <div>
                                <h5 class="topic-title" style="font-weight: 600; font-size: 15px;">{{ $topic->title }}</h5>
                                <p class="topic-description">{{ $topic->description }}</p>
                            </div>

                            {{-- Botón eliminar tema --}}
                            <form action="{{ route('topics.destroy', $topic) }}" method="POST" 
                                onsubmit="return confirm('¿Eliminar este tema y todas sus actividades?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger" title="Eliminar">
                                    <img src="{{ asset('icons/Vector.svg') }}" alt="Eliminar" 
                                        style="width:24px;height:24px" loading="lazy">
                                </button>
                            </form>
                        </div>

                        {{-- Archivo adjunto --}}
                        @if ($topic->file_path)
                            <div class="topic-file">
                                <a href="{{ asset('storage/' . $topic->file_path) }}" target="_blank" class="text-decoration-none">
                                    📎 Ver Archivo Adjunto
                                </a>
                            </div>
                        @endif

                        {{-- Actividades del tema --}}
                        <div class="activities-list" style="margin-bottom: 5px;">
                            @if($topic->activities->count() > 0)
                                <p class="activities-label" style="margin: 0 0 0 10px;">Actividades del tema:</p>
                                @foreach($topic->activities as $activity)
                                    <div class="activity-item">
                                        <span class="activity-type">{{ ucfirst($activity->type) }}</span>
                                        <span class="activity-title">{{ $activity->title }}</span>
                                        <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                            onsubmit="return confirm('¿Eliminar esta actividad?');" class="ms-2">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-activity">&times;</button>
                                        </form>
                                    </div>
                                @endforeach
                            @else
                                <p class="no-activities">No hay actividades para este tema.</p>
                            @endif
                        </div>

                    </div>

                    {{-- Subtemas --}}
                    @if ($topic->subtopics->count() > 0)
                        <div class="subtopics-container">
                            <p class="subtopics-label"></p>
                            @foreach ($topic->subtopics as $subtopic)
                                <div class="subtopic-item"
                                    data-subtopic-id="{{ $subtopic->id }}" 
                                    data-subtopic-title="{{ $subtopic->title }}" 
                                    data-topic-id="{{ $topic->id }}">
                                    
                                    <div class="subtopic-header">    
                                        {{-- Título y descripción --}}
                                        <div>
                                            <h6 class="subtopic-title" style="font-size: 13px">• {{ $subtopic->title }}</h6>
                                            <p class="subtopic-description" style="margin-left: 10px">{{ $subtopic->description }}</p>
                                        </div>
                                        {{-- Botón eliminar Subtema --}}
                                        <form action="{{ route('subtopics.destroy', $subtopic) }}" method="POST" 
                                            onsubmit="return confirm('¿Eliminar este subtema?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger" title="Eliminar">
                                                <img src="{{ asset('icons/Vector.svg') }}" alt="Eliminar" 
                                                    style="width:24px;height:24px" loading="lazy">
                                            </button>
                                        </form>
                                    </div>
                                    {{-- Archivo adjunto --}}
                                    @if ($subtopic->file_path)
                                        <div class="topic-file">
                                            <a href="{{ asset('storage/' . $subtopic->file_path) }}" target="_blank" class="text-decoration-none">
                                                📎 Ver Archivo Adjunto
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Actividades del subtema --}}
                                    @if($subtopic->activities->count() > 0)
                                        <p class="activities-label" style="margin: 0 0 0 10px;">Actividades del subtema:</p>
                                        @foreach($subtopic->activities as $activity)
                                            <div class="activity-item" style="margin-left: 10px;">
                                                <span class="activity-type">{{ ucfirst($activity->type) }}</span>
                                                <span class="activity-title">{{ $activity->title }}</span>
                                                <form action="{{ route('activities.destroy', $activity) }}" method="POST" 
                                                    onsubmit="return confirm('¿Eliminar esta actividad?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="delete-activity">&times;</button>
                                                </form>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="no-topics">
                    <p>Aún no has añadido ningún tema a este curso.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. VARIABLES DE ESTADO
        let selectedTopicId = null;
        let selectedSubtopicId = null;
        let currentMode = 'topic'; // Inicia en modo tema

        // 2. REFERENCIAS DOM
        const modeButtons = document.querySelectorAll('.content-btn button');
        const formContainers = document.querySelectorAll('.form-mode-container');
        const topicCards = document.querySelectorAll('.topic-card');
        const subtopicTopicIdField = document.getElementById('subtopic-topic-id');
        const selectionContextP = document.getElementById('selection-context');

        // 3. FUNCIONES DE LÓGICA
        
        // Muestra el formulario correcto y actualiza los botones de modo
        function setFormMode(mode) {
            currentMode = mode;
            
            // Oculta todos los formularios y resalta el botón de modo activo
            formContainers.forEach(container => container.style.display = 'none');
            modeButtons.forEach(btn => btn.classList.remove('active', 'btn-primary'));

            // Muestra el formulario y resalta el botón
            document.getElementById(`form-${mode}`).style.display = 'block';
            
            const activeBtn = document.getElementById(`mode-${mode}`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'btn-primary');
            }
             if (mode === 'activity') {
                const topicIdField = document.getElementById('activity-topic-id');
                const subtopicIdField = document.getElementById('activity-subtopic-id');

                if (selectedSubtopicId) {
                    // Caso 1: Se seleccionó un Subtema (Actividad pertenece al Subtema)
                    subtopicIdField.value = selectedSubtopicId;
                    topicIdField.value = ''; // Tema debe ser NULL
                    
                } else if (selectedTopicId) {
                    // Caso 2: Solo se seleccionó un Tema (Actividad pertenece al Tema)
                    topicIdField.value = selectedTopicId;
                    subtopicIdField.value = ''; // Subtema debe ser NULL
                    
                } else {
                    // Caso de seguridad: Sin selección (Actividad no se enviará)
                    topicIdField.value = '';
                    subtopicIdField.value = '';
                }
            }
        };
        

        // Actualiza el contexto y habilita/deshabilita botones
        function updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle = null) {
            selectedTopicId = topicId;
            selectedSubtopicId = subtopicId;

            // Limpia la selección visual anterior
            topicCards.forEach(card => card.classList.remove('selected'));
            document.querySelectorAll('.subtopic-item').forEach(card => card.classList.remove('selected'));

            // Actualiza el contexto de selección
            let context = '';
            if (selectedSubtopicId) {
                // Modo Subtema seleccionado
                context = `${topicTitle} > ${subtopicTitle}`;
                document.getElementById('mode-subtopic').disabled = true; // No puedes crear un subtema bajo otro subtema
            } else if (selectedTopicId) {
                // Modo Tema seleccionado (para añadir subtemas o actividades)
                context = topicTitle;
                document.getElementById('mode-subtopic').disabled = false;
            } else {
                // Sin selección (Solo modo Tema)
                context = '';
                document.getElementById('mode-subtopic').disabled = true;
                document.getElementById('mode-activity').disabled = true;
            }
            selectionContextP.textContent = context ? `Selección actual: ${context}` : '';

            // Habilita/Deshabilita el botón de Actividad
            const canAddActivity = selectedTopicId || selectedSubtopicId; // Puedes añadir actividad si hay un tema o subtema seleccionado
            document.getElementById('mode-activity').disabled = !canAddActivity;
        };

        // 4. EVENT LISTENERS

        // Manejar el clic en los botones de modo (+ Tema, + Subtema, + Actividad)
        modeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.dataset.mode;
                
                // Si cambiamos el modo, configuramos el formulario y los datos
                setFormMode(mode);

                if (mode === 'subtopic' && selectedTopicId) {
                    // Si cambiamos a modo Subtema, configuramos la acción y el hidden field
                    const actionRoute = `/topics/${selectedTopicId}/subtopics`; // Usar la URL de la ruta: topics.subtopics.store
                    document.getElementById('subtopic-form').action = actionRoute;
                    subtopicTopicIdField.value = selectedTopicId;

                } else if (mode === 'activity' && (selectedTopicId || selectedSubtopicId)) {
                    // Lógica para modo Actividad (lo implementaremos después)
                }
            });
        });

        // Manejar la selección de Temas y Subtemas en la lista
        topicCards.forEach(card => {
            const topicId = card.dataset.topicId;
            const topicTitle = card.dataset.topicTitle;
            
            // Clic en el Tema
            card.addEventListener('click', function() {
                updateSelectionState(topicId, null, topicTitle);
                this.classList.add('selected');
                setFormMode('subtopic'); // Por convención, al seleccionar un Tema, cambias a modo Subtema
                document.getElementById('subtopic-form').action = `/topics/${selectedTopicId}/subtopics`;
                subtopicTopicIdField.value = selectedTopicId;

            });

            // Lógica para subtemas (debes agregar data-subtopic-id a los divs de subtema)
            const subtopics = card.querySelectorAll('.subtopic-item');
            subtopics.forEach(subcard => {
                const subtopicId = subcard.dataset.subtopicId;
                const subtopicTitle = subcard.dataset.subtopicTitle;
                
                subcard.addEventListener('click', function(e) {
                    e.stopPropagation(); // Evita que se dispare el evento del padre (Topic)
                    updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle);
                    this.classList.add('selected');
                    setFormMode('activity'); // Por convención, al seleccionar un Subtema, cambias a modo Actividad
                });
            });
        });
        
        // Configurar estado inicial
        setFormMode(currentMode);
        
        // 5. LÓGICA ADICIONAL PARA EL FORMULARIO DE ACTIVIDADES
        // Seleccionamos todos los selectores de tipo de actividad
        const selectors = document.querySelectorAll('.activity-type-selector');

        selectors.forEach(selector => {
            selector.addEventListener('change', function () {
                const selectedType = this.value;
                const form = this.closest('form');
                
                // Ocultamos todos los campos de actividad dentro de este formulario
                const allFields = form.querySelectorAll('.activity-fields');

                allFields.forEach(field => {
                    field.style.display = 'none';
                    // Deshabilitamos los inputs para que no se envíen si están ocultos
                    field.querySelectorAll('.form-field-cuestionario').forEach(input => input.required = false);
                });

                // Mostramos los campos del tipo seleccionado
                const activeFields = form.querySelector('#fields-' + selectedType);
                if (activeFields) {
                    activeFields.style.display = 'block';
                    // Habilitamos los inputs para que sean requeridos al mostrarse
                    activeFields.querySelectorAll('.form-field-cuestionario').forEach(input => input.required = true);
                }
            });
        });

    });
</script>
@endpush
@endonce
