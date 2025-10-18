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
                </form>
            </div>
            {{-- 2.4 FORMULARIO DE EDICIÓN DE TEMA (Inicialmente oculto) --}}
            <div id="form-edit-topic" class="form-mode-container" style="display: none;">
                <div class="header-topic">
                    <h3>Editando Tema</h3>
                    <p id="edit-topic-context" style="color: #007bff; font-weight: bold;"></p>
                </div>
                <form id="edit-topic-form" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    {{-- Campo oculto para el ID del tema --}}
                    <input type="hidden" name="topic_id" id="edit-topic-id">
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
                                        data-edit-url="{{ route('topics.edit', $topic) }}"
                                        data-update-url="{{ route('topics.update', $topic) }}"
                                        title="Editar Tema">
                                    <img src="{{ asset('images/icons/pen-to-square-solid-full.svg') }}" alt="Editar"
                                        style="width:24px;height:24px" loading="lazy">
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
</div>
@endsection

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ===================================================
        // 1. VARIABLES DE ESTADO Y REFERENCIAS AL DOM
        // ===================================================
        let selectedTopicId = null;
        let selectedSubtopicId = null;
        let currentMode = 'topic'; // Inicia en modo tema

        const modeButtons = document.querySelectorAll('.content-btn button');
        const formContainers = document.querySelectorAll('.form-mode-container');
        const topicCards = document.querySelectorAll('.topic-card');
        const subtopicTopicIdField = document.getElementById('subtopic-topic-id');
        const selectionContextP = document.getElementById('selection-context');
        const editTopicForm = document.getElementById('edit-topic-form');
        const editTopicContext = document.getElementById('edit-topic-context');
        const currentFileLink = document.getElementById('current-file-link');

        // ===================================================
        // 2. DEFINICIÓN DE FUNCIONES
        // ===================================================

        /**
         * Muestra el formulario correcto según el modo y actualiza los botones.
         */
        function setFormMode(mode) {
            currentMode = mode;
            formContainers.forEach(container => container.style.display = 'none');
            modeButtons.forEach(btn => btn.classList.remove('active', 'btn-primary'));

            // Muestra el formulario correcto (de forma segura)
            const formToShow = document.getElementById(`form-${mode}`);
            if (formToShow) {
                formToShow.style.display = 'block';
            } else {
                console.error(`Error: No se encontró el contenedor del formulario con id: 'form-${mode}'`);
                document.getElementById('form-topic').style.display = 'block'; // Fallback
            }
            
            if (mode !== 'edit-topic'){
                // Resalta el botón de modo correspondiente, si existe
                const activeBtn = document.getElementById(`mode-${mode}`);
                if (activeBtn) {
                    activeBtn.classList.add('active', 'btn-primary');
                }
            }
            

            // Lógica para rellenar IDs en el formulario de actividad
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
        }

        /**
         * Actualiza el estado de la selección (qué tema/subtema está activo).
         */
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
        // 3. ASIGNACIÓN DE EVENT LISTENERS (EJECUCIÓN)
        // ===================================================

        // Listener para los botones de modo (+ Tema, + Subtema, + Actividad)
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

        // Listeners para la selección de Temas y Subtemas
        topicCards.forEach(card => {
            const topicId = card.dataset.topicId;
            const topicTitle = card.dataset.topicTitle;
            
            card.addEventListener('click', function(e) {
                if (e.target.closest('.topic-actions')) {
                    return; 
                }
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

       // --- Listener delegado para los botones de EDITAR tema ---
        document.addEventListener('click', async function(e) {
            const button = e.target.closest('.btn-edit-topic');
            if (!button) return;

            e.stopPropagation();

            const editUrl = button.dataset.editUrl;
            const updateUrl = button.dataset.updateUrl; // ✅ Nueva línea
            const editTopicForm = document.getElementById('edit-topic-form');

            try {
                const response = await fetch(editUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) throw new Error('Error al cargar los datos del tema.');

                const topic = await response.json();

                // Rellenar formulario de edición
                document.getElementById('edit-title').value = topic.title;
                document.getElementById('edit-description').value = topic.description || '';
                document.getElementById('edit-topic-id').value = topic.id;
                document.getElementById('edit-topic-context').textContent = `Editando: "${topic.title}"`;
                
                // ✅ Usar la URL de actualización que ya viene con el parámetro
                editTopicForm.action = updateUrl;

                // Manejar el archivo actual
                const currentFileText = document.getElementById('current-file-text');
                const currentFilePath = document.getElementById('current-file-path');
                
                if (topic.file_path) {
                    currentFileText.innerHTML = `Archivo actual: <a href="/storage/${topic.file_path}" target="_blank">Ver archivo</a>`;
                    currentFilePath.value = topic.file_path;
                } else {
                    currentFileText.innerHTML = 'No hay archivo adjunto actualmente.';
                    currentFilePath.value = '';
                }

                // Mostrar el formulario de edición
                setFormMode('edit-topic');

            } catch (error) {
                console.error('Error al intentar obtener los datos para editar:', error);
                alert('Ocurrió un error al cargar los datos para edición.');
            }
        });

        // Asegurar que el formulario de tema se muestre correctamente al cancelar edición
        document.getElementById('cancel-edit-btn').addEventListener('click', function() {
            setFormMode('topic');
            // Limpiar el formulario de edición
            document.getElementById('edit-topic-form').reset();
            document.getElementById('current-file-text').innerHTML = '';
            document.getElementById('current-file-path').value = '';
        });

        // Listeners para el selector de tipo de actividad
        document.querySelectorAll('.activity-type-selector').forEach(selector => {
            selector.addEventListener('change', function () {
                const selectedType = this.value;
                const form = this.closest('form');
                const allFields = form.querySelectorAll('.activity-fields');

                allFields.forEach(field => {
                    field.style.display = 'none';
                    field.querySelectorAll('.form-field-cuestionario').forEach(input => input.required = false);
                });

                const activeFields = form.querySelector('#fields-' + selectedType);
                if (activeFields) {
                    activeFields.style.display = 'block';
                    activeFields.querySelectorAll('.form-field-cuestionario').forEach(input => input.required = true);
                }
            });
        });

        // ===================================================
        // 4. ESTADO INICIAL
        // ===================================================
        setFormMode(currentMode); // Muestra el formulario de 'topic' al cargar
    });
</script>
@endpush
@endonce
