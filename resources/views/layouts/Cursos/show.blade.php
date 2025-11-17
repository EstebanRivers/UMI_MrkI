@extends('layouts.app')

@section('title', $course->title)

@vite(['resources/css/courseShow.css', 'resources/js/app.js', 'resources/js/bootstrap.js'])

@section('content')
<div class="course-viewer-container">

    {{-- ENCABEZADO --}}
    <header class="course-header">
        <h1>{{ $course->title }}</h1>
        <a href="{{ route('Cursos.index') }}" class="btn-secondary" style="width: fit-content">
            Volver a Cursos
        </a>
    </header>

    <div class="course-layout">
        <div class="course-menu">
        {{-- COLUMNA DERECHA (TEMARIO / NAVEGACIÓN) --}}
        <div class="course-syllabus">
            <h3>Contenido del Curso</h3>

            @foreach ($course->topics as $topic)
                <div class="topic-group">
                    {{-- Tema --}}
                    <strong 
                        @if($topic->file_path) {{-- Si tiene archivo, es "complet-able" --}}
                            class="syllabus-link auto-complete-link accordion-toggle"
                            data-completable-type="Topics"
                            data-completable-id="{{ $topic->id }}"
                        @elseif(!$topic->subtopics->isEmpty() || !$topic->activities->isEmpty()) {{-- Si solo es contenedor --}}
                            class="syllabus-link accordion-toggle"
                        @else {{-- Si está vacío y sin hijos (solo texto) --}}
                            class="syllabus-link completable-text accordion-toggle"
                        @endif
                        data-target="#content-topic-{{ $topic->id }}" 
                        data-target-accordion="#accordion-topic-{{ $topic->id }}">
                        {{ $topic->title }}
                    </strong>
                    <div class="accordion-content" id="accordion-topic-{{ $topic->id }}">
                        {{-- Subtemas --}}
                        @if($topic->subtopics->count() > 0)
                            <ul>
                                @foreach ($topic->subtopics as $subtopic)
                                    <li>
                                        <span 
                                            @if($subtopic->file_path) {{-- Si tiene archivo, es "complet-able" --}}
                                                class="syllabus-link auto-complete-link accordion-toggle"
                                                data-completable-type="Subtopic"
                                                data-completable-id="{{ $subtopic->id }}"
                                            @elseif(!$subtopic->activities->isEmpty()) {{-- Si solo es contenedor --}}
                                                class="syllabus-link accordion-toggle"
                                            @else {{-- Si está vacío (solo texto) --}}
                                                class="syllabus-link completable-text accordion-toggle"
                                            @endif
                                            data-target="#content-subtopic-{{ $subtopic->id }}"
                                            data-target-accordion="#accordion-subtopic-{{ $subtopic->id }}">
                                            {{ $subtopic->title }}
                                        </span>

                                        <div class="accordion-content" id="accordion-subtopic-{{ $subtopic->id }}">
                                            {{-- Actividades del subtema --}}
                                            @if($subtopic->activities->count() > 0)
                                                <ul>
                                                    @foreach ($subtopic->activities as $activity)
                                                        <li class="syllabus-link auto-complete-link" data-target="#content-activity-{{ $activity->id }}"
                                                            data-completable-type="Activities"
                                                            data-completable-id="{{ $activity->id }}">
                                                            - {{ $activity->title }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                        {{-- Actividades del tema --}}
                        @if($topic->activities->count() > 0)
                            <ul>
                                @foreach ($topic->activities as $activity)
                                    <li class="syllabus-link auto-complete-link" data-target="#content-activity-{{ $activity->id }}"
                                        data-completable-type="Activities"
                                        data-completable-id="{{ $activity->id }}">
                                        - {{ $activity->title }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
            @if ($finalExamActivity)
                <div class="topic-group" id="final-exam-syllabus-link" style="display: none; border-top: 3px solid #e69a37; margin-top: 15px; padding-top: 15px;">
                    <strong 
                        class="syllabus-link auto-complete-link accordion-toggle"
                        data-target="#content-activity-{{ $finalExamActivity->id }}"
                        data-completable-type="Activities"
                        data-completable-id="{{ $finalExamActivity->id }}">
                        <span style="font-size: 1.1em;">🎓</span> Examen Final
                    </strong>
                </div>
            @endif
        </div>
        {{-- BARRA DE PROGRESO (al final de .course-syllabus) --}}
            <div class="course-progress-container" 
                id="course-progress-tracker"
                data-total-activities="{{ $totalItems }}"
                data-completed-activities="{{ $completedItems }}">

                <h4>Tu Progreso</h4>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-inner" 
                        id="progress-bar-fill" 
                        style="width: {{ $progress }}%;">
                        
                        <span id="progress-bar-text">{{ $progress }}%</span>

                    </div>
                </div>
            </div>
        {{-- Material de Guía --}}
        @if ($course->guide_material_path)
            <div class="course-guide-material">
                <h4 style="margin-bottom: 10px;">Material de Guía</h4>
                <a href="{{ asset('storage/' . $course->guide_material_path) }}" 
                   target="_blank" 
                   class="btn-secondary" 
                   style="text-decoration: none; display: inline-block; width: 90%;">
                     Descargar Guía del Curso
                </a>
            </div>
        @endif
    </div>

        {{-- COLUMNA IZQUIERDA (VISOR DE CONTENIDO) --}}
        <div class="content-viewer">

            {{-- Contenido por defecto --}}
            <div class="content-panel" id="content-default" style="display: block;">
                <h2>Bienvenido al curso</h2>
                <p>Selecciona un tema, subtema o actividad de la lista de la derecha para comenzar.</p>
                @if($course->image)
                    <img src="{{ asset('storage/' . $course->image) }}" 
                        alt="Portada del curso" class="course-cover" >
                @endif
            </div>

            {{-- Paneles dinámicos --}}
            @foreach ($course->topics as $topic)

                {{-- Panel Tema --}}
                <div class="content-panel" id="content-topic-{{ $topic->id }}">
                     <h2>{{ $topic->title }}</h2>

                    {{-- Descripción y Archivos del Tema --}}
                    <div class="topic-content" >
                        <p>{{ $topic->description }}</p>

                        @if ($topic->file_path)
                            @php
                                $extension = strtolower(pathinfo($topic->file_path, PATHINFO_EXTENSION));
                                $videoExtensions = ['mp4', 'mov', 'webm', 'ogg'];
                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                            @endphp

                            @if ($extension == 'pdf')
                                <div class="file-viewer" style="margin-top: 15px;">
                                    <iframe src="{{ asset('storage/' . $topic->file_path) }}" width="100%" height="600px" style="border: 1px solid #ccc; border-radius: 5px;"></iframe>
                                </div>
                            @elseif (in_array($extension, $videoExtensions))
                                <div class="file-viewer" style="margin-top: 15px;">
                                    <video width="100%" controls style="border-radius: 5px; background: #000;">
                                        <source src="{{ asset('storage/' . $topic->file_path) }}" type="video/{{ $extension }}">
                                        Tu navegador no soporta la reproducción de video.
                                    </video>
                                </div>
                            @elseif (in_array($extension, $imageExtensions))
                                <div class="file-viewer" style="margin-top: 15px;">
                                    <img src="{{ asset('storage/' . $topic->file_path) }}" alt="Material del tema" style="max-width: 100%; border-radius: 8px; border: 1px solid #eee;">
                                </div>
                            @else
                                @php
                                    $fileUrl = asset('storage/' . $topic->file_path);
                                @endphp

                                @if (in_array($extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt']))
                                    <div class="file-viewer" style="margin-top: 15px;">
                                        {{-- Intentar mostrar usando el visor de Google Docs --}}
                                        <iframe 
                                            src="https://docs.google.com/gview?url={{ $fileUrl }}&embedded=true" 
                                            width="100%" 
                                            height="500px" 
                                            style="border: 1px solid #ccc; border-radius: 5px;">
                                        </iframe>
                                    </div>
                                @elseif (in_array($extension, ['zip', 'rar']))
                                    <div class="file-viewer" style="margin-top: 15px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                        <p> Este es un archivo comprimido (<strong>{{ strtoupper($extension) }}</strong>).</p>
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn-secondary">Descargar archivo</a>
                                    </div>
                                @else
                                    <div class="file-viewer" style="margin-top: 15px;">
                                        <iframe src="{{ $fileUrl }}" width="100%" height="600px" style="border: 1px solid #ccc; border-radius: 5px;">
                                        </iframe>
                                    </div>
                                @endif

                            @endif
                        @endif
                    </div>
                </div>

                {{-- Paneles de Subtemas --}}
                @foreach ($topic->subtopics as $subtopic)
                    <div class="content-panel" id="content-subtopic-{{ $subtopic->id }}">
                        <h2>{{ $subtopic->title }}</h2>

                        <div class="subtopic-content" >
                            <p>{{ $subtopic->description }}</p>

                            {{-- Archivos del Subtema --}}
                            @if ($subtopic->file_path)
                                @php
                                    $extension = strtolower(pathinfo($subtopic->file_path, PATHINFO_EXTENSION));
                                    $videoExtensions = ['mp4', 'mov', 'webm', 'ogg'];
                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                                @endphp

                                @if ($extension == 'pdf')
                                    <div class="file-viewer" style="margin-top: 15px;">
                                        <iframe src="{{ asset('storage/' . $subtopic->file_path) }}" width="100%" height="600px" style="border: 1px solid #ccc; border-radius: 5px;"></iframe>
                                    </div>
                                @elseif (in_array($extension, $videoExtensions))
                                    <div class="file-viewer" style="margin-top: 15px;">
                                        <video width="100%" controls style="border-radius: 5px; background: #000;">
                                            <source src="{{ asset('storage/' . $subtopic->file_path) }}" type="video/{{ $extension }}">
                                            Tu navegador no soporta la reproducción de video.
                                        </video>
                                    </div>
                                @elseif (in_array($extension, $imageExtensions))
                                    <div class="file-viewer" style="margin-top: 15px;">
                                        <img src="{{ asset('storage/' . $subtopic->file_path) }}" alt="Material del subtema" style="max-width: 100%; border-radius: 8px; border: 1px solid #eee;">
                                    </div>
                                @else
                                    <a href="{{ asset('storage/' . $subtopic->file_path) }}" target="_blank" class="download-link">
                                        📎 Descargar Material ({{ strtoupper($extension) }})
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- ========================================================== --}}
                    {{-- BLOQUE DE ACTIVIDADES DE SUBTEMA (CORREGIDO) --}}
                    {{-- ========================================================== --}}
                    @foreach ($subtopic->activities as $activity)
                        <div class="content-panel" id="content-activity-{{ $activity->id }}">
                            <h3>{{ $activity->title }} ({{ $activity->type }})</h3>

                            {{-- Render específico por tipo --}}
                            @if ($activity->type == 'Cuestionario' && is_array($activity->content))
                                
                                <form class="quiz-form" id="quiz-form-{{ $activity->id }}"
                                    action="{{ route('activities.submit', $activity) }}" 
                                    method="POST" data-activity-id="{{ $activity->id }}">
                                    @csrf
                                    <p class="question-text">{{ $activity->content['question'] ?? '' }}</p>
                                    @foreach ($activity->content['options'] as $index => $option)
                                        <div class="option-box">
                                            <label>
                                                <input type="radio" name="answer" value="{{ $index }}" {{ Auth::id() == $course->instructor_id ? 'disabled' : '' }}>
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach
                                    <div class="quiz-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>
                                    @if (Auth::id() != $course->instructor_id)
                                        <button type="submit" class="btn-success">Enviar Respuesta</button>
                                    @else
                                        <p class="instructor-note">(Vista de previsualización para el instructor)</p>
                                    @endif
                                </form> 
                            
                            @elseif ($activity->type == 'SopaDeLetras' && is_array($activity->content))
                                <div class="ws-game-container" id="ws-game-{{ $activity->id }}" data-activity-id="{{ $activity->id }}" data-words='@json($activity->content['words'] ?? [])' data-grid-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                    <div class="ws-grid"></div>
                                    <div class="ws-words-list">
                                        <h4>Palabras a encontrar:</h4>
                                        <ul></ul>
                                    </div>
                                </div>
                                <button class="btn-success complete-game-btn" style="margin-top: 15px;" data-activity-id="{{ $activity->id }}" data-submit-url="{{ route('activities.submit', $activity) }}" disabled>Terminado</button>
                                <div class="game-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>
                            
                            @elseif ($activity->type == 'Crucigrama' && is_array($activity->content))
                                
                                <div class="cw-game-layout" id="cw-game-{{ $activity->id }}" data-activity-id="{{ $activity->id }}" data-clues='@json($activity->content['clues'] ?? [])' data-grid-size="{{ $activity->content['grid_size'] ?? 15 }}">
                                    <div class="cw-grid-container">
                                        <div class="cw-grid"></div>
                                    </div>
                                    <div class="cw-clues-container">
                                        <div class="cw-clues-list" id="cw-clues-across">
                                            <h4>Horizontales</h4>
                                            <ul></ul>
                                        </div>
                                        <div class="cw-clues-list" id="cw-clues-down">
                                            <h4>Verticales</h4>
                                            <ul></ul>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn-success complete-game-btn" style="margin-top: 15px;" data-activity-id="{{ $activity->id }}" data-submit-url="{{ route('activities.submit', $activity) }}">Comprobar y Terminar</button>
                                <div class="game-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>

                            @else
                                {{-- Fallback para tipos desconocidos o contenido simple --}}
                                <p>{{ is_array($activity->content) ? json_encode($activity->content) : $activity->content }}</p>
                            @endif
                        </div>
                    @endforeach
                @endforeach

                {{-- ========================================================== --}}
                {{-- BLOQUE DE ACTIVIDADES DE TEMA (CORREGIDO) --}}
                {{-- ========================================================== --}}
                @foreach ($topic->activities as $activity)
                    <div class="content-panel" id="content-activity-{{ $activity->id }}">
                        <h3>{{ $activity->title }} ({{ $activity->type }})</h3>

                        @if ($activity->type == 'Cuestionario' && is_array($activity->content))
                            
                            <form 
                                class="quiz-form"
                                id="quiz-form-{{ $activity->id }}"
                                action="{{ route('activities.submit', $activity) }}" 
                                method="POST"
                                data-activity-id="{{ $activity->id }}">
                                @csrf
                                <p class="question-text">{{ $activity->content['question'] ?? '' }}</p>
                                @foreach ($activity->content['options'] as $index => $option)
                                    <div class="option-box">
                                        <label>
                                            <input type="radio" name="answer" value="{{ $index }}" {{ Auth::id() == $course->instructor_id ? 'disabled' : '' }}>
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach
                                <div class="quiz-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>
                                @if (Auth::id() != $course->instructor_id)
                                    <button type="submit" class="btn-success">Enviar Respuesta</button>
                                @else
                                    <p class="instructor-note">(Vista de previsualización para el instructor)</p>
                                @endif
                            </form>
                        
                        @elseif ($activity->type == 'SopaDeLetras' && is_array($activity->content))
                            <div class="ws-game-container" id="ws-game-{{ $activity->id }}" data-activity-id="{{ $activity->id }}" data-words='@json($activity->content['words'] ?? [])' data-grid-size="{{ $activity->content['grid_size'] ?? 10 }}">
                                <div class="ws-grid"></div>
                                <div class="ws-words-list">
                                    <h4>Palabras a encontrar:</h4>
                                    <ul></ul>
                                </div>
                            </div>
                            <button class="btn-success complete-game-btn" style="margin-top: 15px;" data-activity-id="{{ $activity->id }}" data-submit-url="{{ route('activities.submit', $activity) }}" disabled>Terminado</button>
                            <div class="game-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>

                        @elseif ($activity->type == 'Crucigrama' && is_array($activity->content))
                            <div class="cw-game-container" id="cw-game-{{ $activity->id }}" data-activity-id="{{ $activity->id }}" data-clues='@json($activity->content['clues'] ?? [])' data-grid-size="{{ $activity->content['grid_size'] ?? 15 }}">
                                <div class="cw-grid-container">
                                    <div class="cw-grid"></div>
                                </div>
                                <div class="cw-clues-container">
                                    <div class="cw-clues-list" id="cw-clues-across">
                                        <h4>Horizontales</h4>
                                        <ul></ul>
                                    </div>
                                    <div class="cw-clues-list" id="cw-clues-down">
                                        <h4>Verticales</h4>
                                        <ul></ul>
                                    </div>
                                </div>
                            </div>
                            <button class="btn-success complete-game-btn" style="margin-top: 15px;" data-activity-id="{{ $activity->id }}" data-submit-url="{{ route('activities.submit', $activity) }}">Comprobar y Terminar</button>
                            <div class="game-feedback" id="feedback-{{ $activity->id }}" style="margin-top: 10px;"></div>
                        @else
                            {{-- Fallback --}}
                            <p>{{ is_array($activity->content) ? json_encode($activity->content) : $activity->content }}</p>
                        @endif
                    </div>
                @endforeach

            @endforeach
        </div>

    </div>
</div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        /* ==================================================================
           1. LÓGICA DE PANELES (Sin cambios)
           ================================================================== */
        const links = document.querySelectorAll('.syllabus-link');
        const contentPanels = document.querySelectorAll('.content-panel');
        const syllabusListItems = document.querySelectorAll('.course-syllabus .syllabus-link');

        links.forEach(link => {
            link.addEventListener('click', function () {
                const targetId = this.dataset.target;
                if (!targetId) return; 
                contentPanels.forEach(panel => panel.style.display = 'none');
                const targetPanel = document.querySelector(targetId);
                if (targetPanel) targetPanel.style.display = 'block';
                syllabusListItems.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            });
        });

        /* ==================================================================
           2. LÓGICA DEL ACORDEÓN (Sin cambios)
           ================================================================== */
        const accordions = document.querySelectorAll('.accordion-toggle');
        accordions.forEach(acc => {
            acc.addEventListener('click', function(event) {
                this.classList.toggle('accordion-open');
                const targetId = this.dataset.targetAccordion;
                const content = document.querySelector(targetId);
                if (content) content.classList.toggle('show');
            });
        });

        /* ==================================================================
           3. LÓGICA DE PROGRESO (CORREGIDA)
           ================================================================== */
        // --- Referencia al enlace del examen ---
        const finalExamSyllabusLink = document.getElementById('final-exam-syllabus-link');

        // --- Función para comprobar y mostrar el examen ---
        function checkAndShowFinalExam(progressValue) {
            if (progressValue >= 100 && finalExamSyllabusLink) {
                // Comprobar si ya se mostró para no repetir alertas
                if (finalExamSyllabusLink.style.display === 'none') {
                    finalExamSyllabusLink.style.display = 'block';
                    // Opcional: Mostrar un mensaje
                    alert('¡Felicidades! Has completado el curso y desbloqueado el Examen Final.');
                }
            }
        }

        const markItemAsComplete = (type, id, element) => {
            if (element.classList.contains('completed')) return;
            element.classList.add('completed'); 
            window.axios.post('{{ route("completions.mark") }}', { type: type, id: id })
                .then(response => {
                    if (response.data.success && response.data.created) { 
                        updateProgressBar();
                    }
                })
                .catch(error => {
                    console.error('Error al completar item:', error);
                    element.classList.remove('completed'); 
                });
        };

        const updateProgressBar = () => {
            const tracker = document.getElementById('course-progress-tracker');
            if (!tracker) return;
            const barFill = document.getElementById('progress-bar-fill');
            const barText = document.getElementById('progress-bar-text');
            const completedNow = document.querySelectorAll('.course-syllabus .syllabus-link.completed').length;
            const totalItems = parseInt(tracker.dataset.totalActivities, 10); 
            if (totalItems === 0) {
                checkAndShowFinalExam(100); // Si no hay items, desbloquear
                return;
            };
            let newProgress = Math.round((completedNow / totalItems) * 100);
            if (newProgress > 100) newProgress = 100; // Asegurar el tope
            barFill.style.width = newProgress + '%';
            barText.innerText = newProgress + '%';
            checkAndShowFinalExam(newProgress);
        };

        // --- Escuchar clics en los enlaces "completables" (PDFs/Videos) ---
        const completableLinks = document.querySelectorAll('.auto-complete-link');
        completableLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                const type = this.dataset.completableType;
                const id = this.dataset.completableId;
                if (!type || !id) return;
                
                // --- LÓGICA DE EXCLUSIÓN ACTUALIZADA ---
                let isInteractive = false;
                if (type === 'Activities' && this.dataset.target) {
                    const targetPanel = document.querySelector(this.dataset.target);
                    if (targetPanel) {
                        // VVVVV ESTA ES LA LÍNEA CORREGIDA VVVVV
                        // Comprueba si el panel contiene CUALQUIERA de los juegos
                        if (targetPanel.querySelector('.quiz-form') || 
                            targetPanel.querySelector('.ws-game-container') ||
                            targetPanel.querySelector('.cw-game-layout')) { // <-- AÑADIDO
                            isInteractive = true;
                        }
                        // ^^^^^ ESTA ES LA LÍNEA CORREGIDA ^^^^^
                    }
                }
                
                if (!isInteractive) {
                     markItemAsComplete(type, id, this);
                }
            });
        });

        // --- Marcar Temas/Subtemas de solo texto (feedback visual) ---
        const textLinks = document.querySelectorAll('.completable-text');
        textLinks.forEach(link => {
            link.addEventListener('click', function() { this.classList.add('completed'); });
        });

        // --- Marcar items que YA estaban completas al cargar la página ---
        const userCompletions = @json($userCompletionsMap ?? collect());
        for (const [key, value] of Object.entries(userCompletions)) {
            const parts = key.split('-');
            const type = parts[0].split('\\').pop(); 
            const id = parts[1];
            const link = document.querySelector(`.syllabus-link[data-completable-type="${type}"][data-completable-id="${id}"]`);
            if (link) {
                link.classList.add('completed');
            }
        }
        
        // --- LÓGICA DE ENVÍO DE CUESTIONARIOS (SIN CAMBIOS) ---
        document.querySelectorAll('.quiz-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 
                const activityId = this.dataset.activityId;
                const feedbackEl = document.getElementById(`feedback-${activityId}`);
                const formData = new FormData(this);
                const userAnswer = formData.get('answer');

                if (userAnswer === null) {
                    feedbackEl.style.color = 'red';
                    feedbackEl.innerText = 'Por favor, selecciona una respuesta.';
                    return;
                }
                window.axios.post(this.action, { answer: userAnswer })
                    .then(response => {
                        feedbackEl.style.color = 'green';
                        feedbackEl.innerText = response.data.message;
                        this.querySelectorAll('input, button').forEach(el => el.disabled = true);
                        const syllabusLink = document.querySelector(`.syllabus-link[data-completable-id="${activityId}"][data-completable-type="Activities"]`);
                        if (syllabusLink) syllabusLink.classList.add('completed');
                        if (response.data.created) updateProgressBar();
                    })
                    .catch(error => {
                        feedbackEl.style.color = 'red';
                        if (error.response && error.response.status === 422) {
                            feedbackEl.innerText = error.response.data.message;
                        } else {
                            feedbackEl.innerText = 'Error al enviar la respuesta. Intenta más tarde.';
                        }
                    });
            });
        });

        // --- COMPROBACIÓN INICIAL AL CARGAR PÁGINA ---
        // Obtenemos el progreso inicial que pasó el controlador
        const initialProgress = {{ $progress ?? 0 }};
        checkAndShowFinalExam(initialProgress);
        
        
        /* ==================================================================
           4. LÓGICA NATIVA DE SOPA DE LETRAS (VERSIÓN 2.0 - Sin cambios)
           ================================================================== */
        class WordSearchGame {
            constructor(containerId) {
                this.container = document.getElementById(containerId);
                if (!this.container) return;
                this.activityId = this.container.dataset.activityId;
                this.words = JSON.parse(this.container.dataset.words).map(w => w.toUpperCase());
                this.gridSize = parseInt(this.container.dataset.gridSize, 10);
                this.gridElement = this.container.querySelector('.ws-grid');
                this.wordsListElement = this.container.querySelector('.ws-words-list ul');
                this.completeButton = document.querySelector(`.complete-game-btn[data-activity-id="${this.activityId}"]`);
                this.grid = []; 
                this.foundWords = [];
                this.isSelecting = false;
                this.selection = []; 
                this.startCell = null;
                this.directions = [ { x: 1, y: 0 }, { x: 0, y: 1 }, { x: 1, y: 1 }, { x: 1, y: -1 } ];
                this.init();
            }
            init() {
                this.grid = Array(this.gridSize).fill(null).map(() => Array(this.gridSize).fill(null));
                let allPlaced = this.placeWords();
                if (!allPlaced) console.error("No se pudieron colocar todas las palabras.");
                this.fillEmptyCells();
                this.renderGrid();
                this.renderWordList();
            }
            placeWords() {
                for (const word of this.words) {
                    let placed = false;
                    let attempts = 0;
                    while (!placed && attempts < 50) { 
                        let dir = this.directions[Math.floor(Math.random() * this.directions.length)];
                        if (Math.random() > 0.5) { dir = {x: -dir.x, y: -dir.y}; }
                        let startX = Math.floor(Math.random() * this.gridSize);
                        let startY = Math.floor(Math.random() * this.gridSize);
                        if (this.canPlaceWord(word, startX, startY, dir)) {
                            for (let i = 0; i < word.length; i++) {
                                this.grid[startY + i * dir.y][startX + i * dir.x] = word[i];
                            }
                            placed = true;
                        }
                        attempts++;
                    }
                    if (!placed) return false; 
                }
                return true; 
            }
            canPlaceWord(word, startX, startY, dir) {
                for (let i = 0; i < word.length; i++) {
                    let x = startX + i * dir.x;
                    let y = startY + i * dir.y;
                    if (x < 0 || x >= this.gridSize || y < 0 || y >= this.gridSize) return false;
                    let cell = this.grid[y][x];
                    if (cell !== null && cell !== word[i]) return false;
                }
                return true; 
            }
            fillEmptyCells() {
                const alphabet = "ABCDEFGHIJKLMNÑOPQRSTUVWXYZ"; 
                for (let y = 0; y < this.gridSize; y++) {
                    for (let x = 0; x < this.gridSize; x++) {
                        if (this.grid[y][x] === null) {
                            this.grid[y][x] = alphabet[Math.floor(Math.random() * alphabet.length)];
                        }
                    }
                }
            }
            renderGrid() {
                this.gridElement.innerHTML = ''; 
                this.gridElement.style.setProperty('--ws-grid-size', this.gridSize);
                for (let y = 0; y < this.gridSize; y++) {
                    for (let x = 0; x < this.gridSize; x++) {
                        const cell = document.createElement('div');
                        cell.classList.add('ws-cell');
                        cell.textContent = this.grid[y][x];
                        cell.dataset.x = x;
                        cell.dataset.y = y;
                        cell.addEventListener('mousedown', (e) => this.onMouseDown(e));
                        cell.addEventListener('mousemove', (e) => this.onMouseMove(e));
                        this.gridElement.appendChild(cell);
                    }
                }
                document.addEventListener('mouseup', () => this.onMouseUp());
            }
            renderWordList() {
                this.wordsListElement.innerHTML = '';
                this.words.forEach(word => {
                    const li = document.createElement('li');
                    li.textContent = word;
                    li.id = `ws-${this.activityId}-word-${word}`;
                    this.wordsListElement.appendChild(li);
                });
            }
            onMouseDown(e) {
                if (e.target.classList.contains('found')) return;
                this.isSelecting = true;
                this.selection = [];
                this.startCell = this.getCellFromEvent(e);
                this.addCellToSelection(this.startCell.el);
            }
            onMouseMove(e) {
                if (!this.isSelecting) return;
                this.clearSelectionVisuals();
                this.selection = [this.startCell]; 
                const currentCell = this.getCellFromEvent(e);
                if (currentCell.el === this.startCell.el) {
                    this.addCellToSelection(this.startCell.el);
                    return; 
                }
                let dx = currentCell.x - this.startCell.x;
                let dy = currentCell.y - this.startCell.y;
                const len = Math.max(Math.abs(dx), Math.abs(dy));
                const dirX = Math.round(dx / len) || 0;
                const dirY = Math.round(dy / len) || 0;
                if (dirX === 0 || dirY === 0 || Math.abs(dirX) === Math.abs(dirY)) {
                    for (let i = 1; i <= len; i++) {
                        const cellEl = this.gridElement.querySelector(`.ws-cell[data-x="${this.startCell.x + i * dirX}"][data-y="${this.startCell.y + i * dirY}"]`);
                        if (cellEl && !cellEl.classList.contains('found')) {
                            this.addCellToSelection(cellEl);
                        }
                    }
                } else {
                    this.addCellToSelection(this.startCell.el);
                }
            }
            onMouseUp() {
                if (!this.isSelecting) return;
                this.isSelecting = false;
                this.checkSelection();
                this.clearSelectionVisuals();
                this.selection = [];
                this.startCell = null;
            }
            getCellFromEvent(e) {
                return { x: parseInt(e.target.dataset.x, 10), y: parseInt(e.target.dataset.y, 10), el: e.target };
            }
            addCellToSelection(cellElement) {
                if (!cellElement || this.selection.find(c => c.el === cellElement)) return; 
                cellElement.classList.add('selected');
                this.selection.push(this.getCellFromEvent({ target: cellElement }));
            }
            clearSelectionVisuals() {
                this.selection.forEach(cell => {
                    if (cell.el && !cell.el.classList.contains('found')) {
                        cell.el.classList.remove('selected');
                    }
                });
            }
            checkSelection() {
                if (this.selection.length < 2) return;
                let selectedWord = this.selection.map(c => this.grid[c.y][c.x]).join('');
                let reversedWord = selectedWord.split('').reverse().join('');
                let wordFound = null;
                if (this.words.includes(selectedWord)) wordFound = selectedWord;
                else if (this.words.includes(reversedWord)) wordFound = reversedWord;
                if (wordFound && !this.foundWords.includes(wordFound)) {
                    this.foundWords.push(wordFound);
                    this.selection.forEach(cell => {
                        cell.el.classList.remove('selected');
                        cell.el.classList.add('found');
                    });
                    document.getElementById(`ws-${this.activityId}-word-${wordFound}`).classList.add('found');
                    if (this.foundWords.length === this.words.length) {
                        this.completeButton.disabled = false; 
                    }
                }
            }
        }
        
        /* ==================================================================
           5. LÓGICA NATIVA DE CRUCIGRAMA (NUEVA)
           ================================================================== */
        class CrosswordGame {
            constructor(containerId) {
                this.container = document.getElementById(containerId);
                if (!this.container) return;
                this.activityId = this.container.dataset.activityId;
                this.clues = JSON.parse(this.container.dataset.clues);
                this.gridSize = parseInt(this.container.dataset.gridSize, 10);
                this.gridElement = this.container.querySelector('.cw-grid');
                this.cluesAcrossUl = this.container.querySelector('#cw-clues-across ul');
                this.cluesDownUl = this.container.querySelector('#cw-clues-down ul');
                this.gridMatrix = Array(this.gridSize).fill(null).map(() => Array(this.gridSize).fill(null));
                this.inputs = []; 
                this.init();
            }
            init() {
                this.buildGridMatrix();
                this.renderGrid();
                this.renderClues();
                this.addInputListeners();
            }
            buildGridMatrix() {
                const allClues = [
                    ...(this.clues.across || []).map(c => ({...c, dir: 'across'})),
                    ...(this.clues.down || []).map(c => ({...c, dir: 'down'}))
                ];
                allClues.forEach(clue => {
                    let { answer, x, y, dir } = clue;
                    for (let i = 0; i < answer.length; i++) {
                        let currentX = x + (dir === 'across' ? i : 0);
                        let currentY = y + (dir === 'down' ? i : 0);
                        if (currentY < this.gridSize && currentX < this.gridSize) {
                            this.gridMatrix[currentY][currentX] = answer[i].toUpperCase();
                        }
                    }
                });
            }
            renderGrid() {
                this.gridElement.innerHTML = '';
                this.gridElement.style.setProperty('--cw-grid-size', this.gridSize);
                const clueNumbers = new Map();
                (this.clues.across || []).forEach(c => clueNumbers.set(`${c.x},${c.y}`, c.number));
                (this.clues.down || []).forEach(c => clueNumbers.set(`${c.x},${c.y}`, c.number));
                for (let y = 0; y < this.gridSize; y++) {
                    for (let x = 0; x < this.gridSize; x++) {
                        const cell = document.createElement('div');
                        cell.classList.add('cw-cell');
                        const correctLetter = this.gridMatrix[y][x];
                        if (correctLetter) {
                            cell.classList.add('white');
                            const number = clueNumbers.get(`${x},${y}`);
                            if (number) cell.innerHTML = `<span class="cw-number">${number}</span>`;
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.maxLength = 1;
                            input.dataset.x = x;
                            input.dataset.y = y;
                            input.dataset.correct = correctLetter;
                            cell.appendChild(input);
                            this.inputs.push(input);
                        }
                        this.gridElement.appendChild(cell);
                    }
                }
            }
            renderClues() {
                (this.clues.across || []).forEach(c => {
                    const li = document.createElement('li');
                    li.textContent = `${c.number}. ${c.clue}`;
                    this.cluesAcrossUl.appendChild(li);
                });
                (this.clues.down || []).forEach(c => {
                    const li = document.createElement('li');
                    li.textContent = `${c.number}. ${c.clue}`;
                    this.cluesDownUl.appendChild(li);
                });
            }
            addInputListeners() {
                this.inputs.forEach(input => {
                    input.addEventListener('keyup', (e) => {
                        // Mover foco al siguiente input al escribir una letra
                        if (e.key.length === 1 && e.key.match(/[a-zA-ZñÑ]/)) {
                            const nextInput = this.findNextInput(input);
                            if (nextInput) nextInput.focus();
                        }
                    });
                    input.addEventListener('keydown', (e) => {
                        let x = parseInt(input.dataset.x);
                        let y = parseInt(input.dataset.y);
                        switch(e.key) {
                            case 'ArrowRight': e.preventDefault(); this.findNextInput(input, x + 1, y)?.focus(); break;
                            case 'ArrowLeft': e.preventDefault(); this.findNextInput(input, x - 1, y, true)?.focus(); break;
                            case 'ArrowDown': e.preventDefault(); this.findNextInput(input, x, y + 1)?.focus(); break;
                            case 'ArrowUp': e.preventDefault(); this.findNextInput(input, x, y - 1, true)?.focus(); break;
                        }
                    });
                });
            }
            findNextInput(currentInput, startX, startY, reverse = false) {
                const currentIndex = this.inputs.indexOf(currentInput);
                let nextInput = null;
                // Lógica simple (solo mueve al siguiente/anterior en el array)
                if (reverse) {
                    if (currentIndex > 0) nextInput = this.inputs[currentIndex - 1];
                } else {
                    if (currentIndex < this.inputs.length - 1) nextInput = this.inputs[currentIndex + 1];
                }
                return nextInput;
            }
        }
        
        /* ==================================================================
           6. INICIALIZACIÓN DE JUEGOS Y BOTONES (CORREGIDO Y LIMPIO)
           ================================================================== */
        
        // --- INICIALIZAR TODOS LOS JUEGOS ---
        document.querySelectorAll('.ws-game-container').forEach(container => {
            new WordSearchGame(container.id);
        });
        document.querySelectorAll('.cw-game-container').forEach(container => { 
            new CrosswordGame(container.id);
        });
        
        // --- LISTENER ÚNICO PARA TODOS LOS BOTONES DE JUEGO ---
        document.querySelectorAll('.complete-game-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const activityId = this.dataset.activityId;
                const url = this.dataset.submitUrl;
                const feedbackEl = document.getElementById(`feedback-${activityId}`);
                
                let gameContainer = document.getElementById(`ws-game-${activityId}`);
                let activityType = 'SopaDeLetras';
                if (!gameContainer) {
                    gameContainer = document.getElementById(`cw-game-${activityId}`);
                    activityType = 'Crucigrama';
                }

                // --- VALIDACIÓN ANTES DE ENVIAR ---
                if (activityType === 'Crucigrama') {
                    if (!checkCrosswordWin(gameContainer)) {
                        feedbackEl.style.color = 'red';
                        feedbackEl.innerText = 'Respuestas incorrectas. Revisa las celdas rojas.';
                        return; 
                    }
                }
                // (La Sopa de Letras no necesita validación aquí porque el botón está 'disabled')
                
                this.disabled = true; 
                window.axios.post(url, {})
                    .then(response => {
                        feedbackEl.style.color = 'green';
                        feedbackEl.innerText = response.data.message;
                        const syllabusLink = document.querySelector(`.syllabus-link[data-completable-id="${activityId}"][data-completable-type="Activities"]`);
                        if (syllabusLink) syllabusLink.classList.add('completed');
                        if (response.data.created) updateProgressBar();
                    })
                    .catch(error => {
                        feedbackEl.style.color = 'red';
                        feedbackEl.innerText = 'Error al guardar tu progreso. Intenta de nuevo.';
                        this.disabled = false; // Permitir reintento
                    });
            });
        });

        // --- FUNCIÓN DE AYUDA PARA VALIDAR CRUCIGRAMA ---
        function checkCrosswordWin(container) {
            let allCorrect = true;
            container.querySelectorAll('.cw-cell input').forEach(input => {
                const correct = input.dataset.correct.toUpperCase();
                const answer = input.value.toUpperCase();
                if (correct !== answer) {
                    input.style.backgroundColor = '#f8d7da'; 
                    allCorrect = false;
                } else {
                    input.style.backgroundColor = '#d4edda'; 
                    input.disabled = true; 
                }
            });
            return allCorrect;
        }

    }); // Cierre del DOMContentLoaded
</script>
@endpush
@endsection