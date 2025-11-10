@extends('layouts.app')

@section('title', 'Añadir Temas a ' . $course->title)

@vite(['resources/css/Cursos/topic.css','resources/js/app.js'])

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
        <a href="{{ route('Cursos.index') }}" class="btn-secondary">
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
                        <label for="activity_type">Tipo de Actividad</label>
                        <select name="type" id="activity_type" required>
                            <option value="" disabled selected>Selecciona un tipo</option>
                            <option value="Cuestionario">Cuestionario (Quiz)</option>
                            <option value="SopaDeLetras">Sopa de Letras</option>
                            <option value="Crucigrama">Crucigrama</option>
                        </select>
                    </div>

                    <div id="activity-type-container">

                        <div id="template-Cuestionario" class="activity-template" style="display: none;">
                            <div class="activity-fields-container">
                                <div class="activity-fields" id="fields-Cuestionario"> <div class="form-group">
                                        <label>Pregunta del cuestionario:</label>
                                        <input type="text" name="content[question]" class="form-field-cuestionario" placeholder="Escribe la pregunta aquí" disabled>
                                    </div>
                                </div>
                                <label>Opciones de respuesta (marca la correcta):</label>
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="quiz-option">
                                        <input type="radio" name="content[correct_answer]" value="{{ $i }}" disabled>
                                        <input type="text" name="content[options][]" class="form-field-cuestionario" placeholder="Opción {{ $i + 1 }}" disabled>
                                    </div>
                                @endfor
                            </div>
                        </div>
                        <div id="template-SopaDeLetras" class="activity-template" style="display: none;">
                            
                            <div class="form-group">
                                <label for="content_grid_size">Tamaño de Cuadrícula (Ej: 10 para 10x10)</label>
                                <input type="number" name="content[grid_size]" id="content_grid_size" 
                                    value="10" min="5" max="20" disabled>
                            </div>
                            
                            <div class="form-group">
                                <label for="ws_word_input">Palabras a encontrar</label>
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" id="ws_word_input" 
                                        placeholder="Escribe una palabra y presiona 'Añadir'" 
                                        style="flex: 1;" disabled>
                                    <button type="button" id="ws_add_word_btn" class="btn-secondary" disabled>Añadir</button>
                                </div>
                                <small>Se recomiendan palabras sin espacios ni acentos, todo en mayúsculas.</small>
                            </div>

                            <label>Palabras añadidas:</label>
                            <ul id="ws_word_list" style="list-style: disc; margin-left: 20px; min-height: 50px; background: #f4f4f4; border-radius: 4px; padding: 10px;"></ul>
                            
                            <div id="ws_hidden_inputs"></div>

                        </div>
                        <div id="template-Crucigrama" class="activity-template" style="display: none;">
                            <div class="form-group">
                                <label for="cw_grid_size">Tamaño de Cuadrícula (Ej: 15 para 15x15)</label>
                                <input type="number" name="content[grid_size]" id="cw_grid_size" 
                                    value="15" min="5" max="25" disabled>
                            </div>

                            <div id="cw-editor-container">
                                <div>
                                    <label>Haz clic en una celda para elegir la posición:</label>
                                    <div id="cw-editor-grid">
                                        </div>
                                </div>

                                <div style="flex: 1; min-width: 250px;">
                                    <fieldset style="border: 1px solid #ccc; padding: 10px; border-radius: 4px;">
                                        <legend style="font-size: 1em; padding: 0 5px; width: auto;">Añadir Pista</legend>
                                        
                                        <div class="form-group">
                                            <label>Posición Seleccionada (X, Y):</label>
                                            <input type="text" id="cw_coords_display" value="0, 0" readonly disabled 
                                                style="background: #eee; font-weight: bold;">
                                        </div>

                                        <div class="form-group">
                                            <label for="cw_direction">Dirección</label>
                                            <select id="cw_direction" style="width: 100%;">
                                                <option value="across">Horizontal (Across)</option>
                                                <option value="down">Vertical (Down)</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cw_number">Número</label>
                                            <input type="number" id="cw_number" min="1" value="1" style="width: 100%;">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cw_clue">Pista</label>
                                            <input type="text" id="cw_clue" placeholder="Ej: Capital de Francia">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="cw_answer">Respuesta (sin espacios, en mayúsculas)</label>
                                            <input type="text" id="cw_answer" placeholder="Ej: PARIS">
                                        </div>
                                        
                                        <button type="button" id="cw_add_clue_btn" class="btn-secondary" style="width: 100%;">+ Añadir Pista</button>
                                    </fieldset>
                                </div>
                            </div>

                            <div style="display: flex; gap: 20px; margin-top: 15px;">
                                <div style="flex: 1;">
                                    <label>Horizontales:</label>
                                    <ul id="cw_across_list" class="cw-clue-list"></ul>
                                </div>
                                <div style="flex: 1;">
                                    <label>Verticales:</label>
                                    <ul id="cw_down_list" class="cw-clue-list"></ul>
                                </div>
                            </div>
                            
                            <div id="cw_hidden_inputs"></div>
                            
                        </div>
                    </div> 
                </form>
            </div>
            {{-- 2.4 FORMULARIO DE EDICIÓN DE TEMA (Inicialmente oculto) --}}
            <div id="form-edit-topic" class="form-mode-container" style="display: none;">
                <div class="header-topic" style="display:flex; justify-content: space-between;">
                    <h3>Editando Tema</h3>
                    <p id="edit-topic-context" style="color: #007bff; font-weight: bold;"></p>
                </div>
                <form id="edit-topic-form" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    {{-- Campo oculto para el ID del tema --}}
                    <input type="hidden" name="topic_id" id="edit-topic-id" value="PUT">
                    <input type="hidden" name="course_id" value="{{ $course->id }}">

                    {{-- Título --}}
                    <div class="form-group">
                        <label for="edit-title">Título del Tema</label>
                        <input type="text" id="edit-title" name="title" required>
                    </div>

                    {{-- Descripción --}}
                    <div class="form-group">
                        <label for="edit-description">Descripción Detallada del Tema</label>
                        <textarea id="edit-description" name="description" rows="5"></textarea>
                    </div>

                    {{-- Archivo --}}
                    <div class="form-group">
                        <label for="edit-file">Reemplazar Archivo (Opcional)</label>
                        <input type="file" id="edit-file" name="file">
                        <div id="current-file-info" style="margin-top: 5px;">
                            <small id="current-file-text"></small>
                            {{-- Campo oculto para mantener el file_path actual si no se sube nuevo archivo --}}
                            <input type="hidden" name="current_file_path" id="current-file-path">
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-success">Guardar Cambios</button>
                        <button type="button" id="cancel-edit-btn" class="btn-secondary">Cancelar</button>
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

                            <div class="topic-actions"> 
                                {{-- BOTÓN DE EDITAR --}}
                                <button type="button" class="btn-edit-topic"
                                        data-id="{{ $topic->id }}"
                                        data-title="{{ $topic->title }}"
                                        data-description="{{ $topic->description }}"
                                        data-file-path="{{ $topic->file_path }}"
                                        data-update-url="{{ route('topics.update', $topic->id) }}"
                                        title="Editar Tema">
                                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" 
                                        alt="Editar" style="width:24px;height:24px" loading="lazy">
                                </button>


                                {{-- Botón eliminar tema --}}
                                <form action="{{ route('topics.destroy', $topic) }}" method="POST" 
                                    onsubmit="return confirm('¿Eliminar este tema y todas sus actividades?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger" title="Eliminar">
                                        <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
                                            style="width:24px;height:24px" loading="lazy">
                                    </button>
                                </form>
                            </div>
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
                                                <img src="{{ asset('images/icons/Vector.svg') }}" alt="Eliminar" 
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
document.addEventListener('DOMContentLoaded', function() {
    // ===================================================
    // 1. VARIABLES DE ESTADO Y REFERENCIAS AL DOM
    // ===================================================
    let selectedTopicId = null;
    let selectedSubtopicId = null;
    let currentMode = 'topic';

    const modeButtons = document.querySelectorAll('.content-btn button');
    const formContainers = document.querySelectorAll('.form-mode-container');
    const topicCards = document.querySelectorAll('.topic-card');
    const subtopicTopicIdField = document.getElementById('subtopic-topic-id');
    const selectionContextP = document.getElementById('selection-context');
    const editTopicForm = document.getElementById('edit-topic-form');

    // ===================================================
    // 2. FUNCIÓN setFormMode
    // ===================================================
    function setFormMode(mode) {
    console.log("→ setFormMode activado con modo:", mode);

    currentMode = mode;

    // 🔹 Oculta todos los formularios
    formContainers.forEach(container => container.style.display = 'none');

    // 🔹 Quita clases y controla botones
    modeButtons.forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.disabled = (mode === 'edit-topic'); // Solo desactiva si estás editando
    });

    // 🔹 Mostrar formulario correcto
    const formToShow = document.getElementById(`form-${mode}`);
    if (formToShow) {
        console.log(`→ Mostrando formulario: form-${mode}`);
        formToShow.style.display = 'block';
    } else {
        console.warn(`⚠ No se encontró form-${mode}, mostrando form-topic por defecto`);
        document.getElementById('form-topic').style.display = 'block';
    }

    // 🔹 Solo marcar botón activo si no estamos editando
    if (mode !== 'edit-topic') {
        const activeBtn = document.getElementById(`mode-${mode}`);
        if (activeBtn) {
            activeBtn.classList.add('active', 'btn-primary');
        }
    }

    // 🔹 Asignar IDs de tema/subtema para actividades
    if (mode === 'activity') {
        const topicIdField = document.getElementById('activity-topic-id');
        const subtopicIdField = document.getElementById('activity-subtopic-id');
        if (selectedSubtopicId) {
            subtopicIdField.value = selectedSubtopicId;
            topicIdField.value = '';
        } else if (selectedTopicId) {
            topicIdField.value = selectedTopicId;
            subtopicIdField.value = '';
        }
    }

    // 🔹 Confirmación visual
    console.log("✔ Formulario mostrado correctamente:", formToShow ? formToShow.id : 'ninguno');
}


    // ===================================================
    // 3. FUNCIÓN updateSelectionState
    // ===================================================
    function updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle = null) {
        selectedTopicId = topicId;
        selectedSubtopicId = subtopicId;

        topicCards.forEach(card => card.classList.remove('selected'));
        document.querySelectorAll('.subtopic-item').forEach(card => card.classList.remove('selected'));

        let context = '';
        if (selectedSubtopicId) {
            context = `${topicTitle} > ${subtopicTitle}`;
            document.getElementById('mode-subtopic').disabled = true;
        } else if (selectedTopicId) {
            context = topicTitle;
            document.getElementById('mode-subtopic').disabled = false;
        } else {
            document.getElementById('mode-subtopic').disabled = true;
        }
        selectionContextP.textContent = context ? `Selección actual: ${context}` : '';
        
        const canAddActivity = selectedTopicId || selectedSubtopicId;
        document.getElementById('mode-activity').disabled = !canAddActivity;
    }

    // ===================================================
    // 4. EVENTOS DE MODO
    // ===================================================
    modeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            setFormMode(mode);

            if (mode === 'subtopic' && selectedTopicId) {
                document.getElementById('subtopic-form').action = `/topics/${selectedTopicId}/subtopics`;
                subtopicTopicIdField.value = selectedTopicId;
            }
        });
    });

    // ===================================================
    // 5. SELECCIÓN DE TEMAS Y SUBTEMAS
    // ===================================================
    topicCards.forEach(card => {
        const topicId = card.dataset.topicId;
        const topicTitle = card.dataset.topicTitle;
        
        card.addEventListener('click', function(e) {
            if (e.target.closest('.topic-actions')) return;
            updateSelectionState(topicId, null, topicTitle);
            this.classList.add('selected');
            setFormMode('subtopic');
            document.getElementById('subtopic-form').action = `/topics/${selectedTopicId}/subtopics`;
            subtopicTopicIdField.value = selectedTopicId;
        });

        card.querySelectorAll('.subtopic-item').forEach(subcard => {
            const subtopicId = subcard.dataset.subtopicId;
            const subtopicTitle = subcard.dataset.subtopicTitle;
            
            subcard.addEventListener('click', function(e) {
                e.stopPropagation();
                updateSelectionState(topicId, subtopicId, topicTitle, subtopicTitle);
                this.classList.add('selected');
                setFormMode('activity');
            });
        });
    });

    // ===================================================
    // 6. SELECTOR DE TIPO DE ACTIVIDAD (CORREGIDO)
    // ===================================================
    const activityTypeSelect = document.getElementById('activity_type'); // <- Corregido: Usar el ID

    if (activityTypeSelect) {
        activityTypeSelect.addEventListener('change', function () {
            const selectedType = this.value; // ej: "Cuestionario" o "SopaDeLetras"
            const form = this.closest('form');
            
            // 1. Ocultar TODAS las plantillas
            const allTemplates = form.querySelectorAll('.activity-template');
            allTemplates.forEach(template => {
                template.style.display = 'none';
                
                // Deshabilitar todos sus campos para que no se envíen
                template.querySelectorAll('input, button, select, textarea').forEach(input => {
                    input.disabled = true;
                });
            });

            // 2. Mostrar la plantilla seleccionada
            const activeTemplate = form.querySelector('#template-' + selectedType);
            if (activeTemplate) {
                activeTemplate.style.display = 'block';
                
                // Habilitar solo sus campos
                activeTemplate.querySelectorAll('input, button, select, textarea').forEach(input => {
                    input.disabled = false;
                });
                if (selectedType === 'Crucigrama') {
                    const gridSize = document.getElementById('cw_grid_size').value;
                    drawEditorGrid(gridSize);
                }
            }
        });
    }

    // Lógica para el formulario de Sopa de Letras (Esta parte ya estaba bien)
    const addWordBtn = document.getElementById('ws_add_word_btn');
    const wordInput = document.getElementById('ws_word_input');
    const wordList = document.getElementById('ws_word_list');
    const hiddenInputsContainer = document.getElementById('ws_hidden_inputs');

    if (addWordBtn) {
        
        // Función para añadir la palabra
        const addWord = () => {
            let word = wordInput.value.trim().toUpperCase();
            
            // Validar (simple)
            if (word === '' || word.includes(' ')) {
                alert('Por favor, escribe una sola palabra sin espacios.');
                return;
            }

            // 1. Crear el input oculto para el formulario
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'content[words][]'; // Esto crea el array en PHP
            hiddenInput.value = word;
            hiddenInputsContainer.appendChild(hiddenInput);

            // 2. Crear el elemento <li> para que el usuario lo vea
            const li = document.createElement('li');
            li.textContent = word;

            // 3. (Opcional) Añadir botón de eliminar
            const removeBtn = document.createElement('span');
            removeBtn.textContent = ' [X]';
            removeBtn.style.color = 'red';
            removeBtn.style.cursor = 'pointer';
            removeBtn.onclick = () => {
                hiddenInputsContainer.removeChild(hiddenInput);
                wordList.removeChild(li);
            };
            li.appendChild(removeBtn);
            
            wordList.appendChild(li);

            // 4. Limpiar el input
            wordInput.value = '';
            wordInput.focus();
        };

        // Añadir al hacer clic
        addWordBtn.addEventListener('click', addWord);
        
        // Añadir al presionar Enter
        wordInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evitar que el formulario se envíe
                addWord();
            }
        });
    }

    // ===================================================
    // 7. LÓGICA DEL EDITOR DE CRUCIGRAMA (NUEVO)
    // ===================================================

    // --- Variables Globales del Editor ---
    const editorGrid = document.getElementById('cw-editor-grid');
    const gridSizeInput = document.getElementById('cw_grid_size');
    const coordsDisplay = document.getElementById('cw_coords_display');
    const addClueBtn = document.getElementById('cw_add_clue_btn');
    const cwHiddenInputs = document.getElementById('cw_hidden_inputs');
    let activeCell = { x: 0, y: 0 }; // Guarda la (X, Y) seleccionada
    let editorCells = []; // Para acceder a las celdas

    // --- Función para Dibujar la Cuadrícula del Editor ---
    function drawEditorGrid(size) {
        if (!editorGrid) return;
        editorGrid.innerHTML = '';
        editorGrid.style.setProperty('--cw-editor-grid-size', size);
        editorCells = []; // Limpiar la referencia

        for (let y = 0; y < size; y++) {
            let row = [];
            for (let x = 0; x < size; x++) {
                const cell = document.createElement('div');
                cell.classList.add('cw-editor-cell');
                cell.dataset.x = x;
                cell.dataset.y = y;

                // Evento de clic para seleccionar la celda
                cell.addEventListener('click', () => {
                    // Quitar 'selected' de la celda anterior
                    const oldSelected = editorGrid.querySelector('.selected');
                    if (oldSelected) oldSelected.classList.remove('selected');
                    
                    // Añadir 'selected' a la nueva
                    cell.classList.add('selected');
                    activeCell = { x: x, y: y }; // ¡Guardar coordenadas!
                    
                    // Actualizar el display
                    if(coordsDisplay) coordsDisplay.value = `${x}, ${y}`;
                });
                
                editorGrid.appendChild(cell);
                row.push(cell);
            }
            editorCells.push(row);
        }
        
        // Seleccionar (0,0) por defecto
        if (editorCells.length > 0) {
            editorCells[0][0].classList.add('selected');
            if(coordsDisplay) coordsDisplay.value = '0, 0';
            activeCell = { x: 0, y: 0 };
        }
    }

    // --- Función para Escribir una palabra en el Editor Visual ---
    function placeWordOnEditor(word, x, y, direction, number) {
        let placed = true;
        for (let i = 0; i < word.length; i++) {
            let currentX = x + (direction === 'across' ? i : 0);
            let currentY = y + (direction === 'down' ? i : 0);
            
            // 1. Comprobar límites
            if (currentY >= editorCells.length || currentX >= editorCells[0].length) {
                alert(`Error: La palabra "${word}" se sale de la cuadrícula.`);
                return false;
            }
            
            const cell = editorCells[currentY][currentX];
            const letter = word[i].toUpperCase();

            // 2. Comprobar colisiones
            if (cell.textContent !== '' && cell.textContent !== letter) {
                alert(`Error: La letra "${letter}" choca con "${cell.textContent}" en [${currentX},${currentY}].`);
                return false;
            }
        }
        
        // Si no hubo errores, escribir la palabra
        for (let i = 0; i < word.length; i++) {
            let currentX = x + (direction === 'across' ? i : 0);
            let currentY = y + (direction === 'down' ? i : 0);
            const cell = editorCells[currentY][currentX];
            
            // Poner la letra
            cell.textContent = word[i].toUpperCase();
            
            // Poner el número (solo en la primera celda)
            if (i === 0 && !cell.querySelector('.cw-editor-number')) {
                cell.innerHTML += `<span class="cw-editor-number">${number}</span>`;
            }
        }
        return true;
    }

    // --- Event Listener para el tamaño de la cuadrícula ---
    if (gridSizeInput) {
        gridSizeInput.addEventListener('change', () => {
            // (Advertencia: esto borrará el crucigrama si ya se empezó)
            if (confirm('Cambiar el tamaño borrará el crucigrama actual. ¿Continuar?')) {
                drawEditorGrid(gridSizeInput.value);
                // También deberías limpiar las listas y los inputs ocultos
                document.getElementById('cw_across_list').innerHTML = '';
                document.getElementById('cw_down_list').innerHTML = '';
                cwHiddenInputs.innerHTML = '';
            } else {
                // Revertir
            }
        });
    }

    // --- Event Listener para el botón "+ Añadir Pista" (MODIFICADO) ---
    if (addClueBtn) {
        let counters = { across: 0, down: 0 };

        addClueBtn.addEventListener('click', function() {
            // 1. Obtener valores del formulario
            const direction = document.getElementById('cw_direction').value;
            const number = document.getElementById('cw_number').value;
            const clue = document.getElementById('cw_clue').value;
            const answer = document.getElementById('cw_answer').value.toUpperCase();
            
            // 2. OBTENER (X, Y) DEL EDITOR, NO DE INPUTS
            const x = activeCell.x;
            const y = activeCell.y;

            if (!number || !clue || !answer || x === null || y === null) {
                alert('Por favor, rellena todos los campos de la pista y selecciona una celda.');
                return;
            }
            if (answer.includes(' ') || answer.length === 0) {
                alert('La respuesta debe ser una sola palabra sin espacios.');
                return;
            }
            
            // 3. Dibujar la palabra en el editor
            if (!placeWordOnEditor(answer, x, y, direction, number)) {
                return; // Detener si la palabra no se pudo colocar
            }

            // --- El resto de la lógica es la misma que ya tenías ---
            
            const listId = `cw_${direction}_list`; 
            const listElement = document.getElementById(listId);
            const index = counters[direction]++; 
            
            // 4. Crear los inputs ocultos
            const prefix = `content[clues][${direction}][${index}]`;
            cwHiddenInputs.insertAdjacentHTML('beforeend', `
                <input type="hidden" name="${prefix}[number]" value="${number}">
                <input type="hidden" name="${prefix}[clue]" value="${clue}">
                <input type="hidden" name="${prefix}[answer]" value="${answer}">
                <input type="hidden" name="${prefix}[x]" value="${x}">
                <input type="hidden" name="${prefix}[y]" value="${y}">
            `);

            // 5. Crear el <li> visible
            const li = document.createElement('li');
            li.textContent = `${number}. [${x},${y}] ${clue} (${answer})`;
            
            const removeBtn = document.createElement('span');
            removeBtn.textContent = ' [X]';
            removeBtn.style.color = 'red';
            removeBtn.style.cursor = 'pointer';
            li.appendChild(removeBtn);

            removeBtn.addEventListener('click', () => {
                // (Nota: Esto elimina la pista de la lista, pero no
                // la borra del editor visual. Se necesitaría lógica adicional
                // para "limpiar" las celdas de la cuadrícula)
                cwHiddenInputs.querySelectorAll(`input[name^="${prefix}"]`).forEach(inp => inp.remove());
                listElement.removeChild(li);
            });

            listElement.appendChild(li);

            // 6. Limpiar formulario
            document.getElementById('cw_clue').value = '';
            document.getElementById('cw_answer').value = '';
            document.getElementById('cw_number').value = parseInt(number) + 1;
            document.getElementById('cw_clue').focus();
        });
    }

    // ===================================================
    // 7. DELEGACIÓN DE EVENTO: EDITAR TEMA
    // ===================================================
    document.addEventListener('click', function (event) {
        if (event.target.closest('.btn-edit-topic')) {
            console.log("✅ Click detectado en botón editar tema");
            const btn = event.target.closest('.btn-edit-topic');
            console.log("Datos del botón:", btn.dataset);

            const topicId = btn.dataset.id;
            const title = btn.dataset.title;
            const description = btn.dataset.description;
            const filePath = btn.dataset.filePath;
            const updateUrl = btn.dataset.updateUrl;

            console.log({ topicId, title, description, filePath, updateUrl });

            // Verificar existencia del formulario
            const editForm = document.getElementById('form-edit-topic');
            if (!editForm) {
                console.error("❌ No se encontró el formulario de edición (id='form-edit-topic')");
                return;
            }

            editForm.querySelector('form').action = updateUrl;

            // Llenar formulario
            document.getElementById('edit-topic-id').value = topicId || '';
            document.getElementById('edit-title').value = title || '';
            document.getElementById('edit-description').value = description || '';
            document.getElementById('current-file-path').value = filePath || '';

            const currentFileText = document.getElementById('current-file-text');
            if (currentFileText) {
                currentFileText.textContent = filePath
                    ? `Archivo actual: ${filePath.split('/').pop()}`
                    : 'No hay archivo adjunto.';
            }

            // Cambiar el modo
            console.log("Cambiando a modo edición...");
            if (typeof setFormMode === "function") {
                setFormMode('edit-topic');
            } else {
                console.error("⚠️ La función setFormMode no está definida o no es global.");
            }
        }
    });


    // ===================================================
    // 9. ESTADO INICIAL
    // ===================================================
    setFormMode(currentMode);
});
</script>

@endpush
@endonce
