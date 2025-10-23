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
                            </div>
                                <label>Opciones de respuesta (marca la correcta):</label>
                                @for ($i = 0; $i < 4; $i++)
                                    <div class="quiz-option">
                                        <input type="radio" name="content[correct_answer]" value="{{ $i }}">
                                        <input type="text" name="content[options][]" class="form-field-cuestionario" placeholder="Opción {{ $i + 1 }}">
                                    </div>
                                @endfor
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
    // 6. SELECTOR DE TIPO DE ACTIVIDAD
    // ===================================================
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
