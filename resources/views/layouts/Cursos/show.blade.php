@extends('layouts.app')

@section('title', $course->title)

@vite(['resources/css/courseShow.css', 'resources/js/app.js'])

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

        {{-- COLUMNA DERECHA (TEMARIO / NAVEGACIÓN) --}}
        <div class="course-syllabus">
            <h3>Contenido del Curso</h3>

            @foreach ($course->topics as $topic)
                <div class="topic-group">
                    {{-- Tema --}}
                    <strong 
                        @if($topic->activities->isEmpty() && $topic->subtopics->isEmpty())
                            class="syllabus-link completable-text accordion-toggle"
                        @else
                            class="syllabus-link accordion-toggle"
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
                                            @if($subtopic->activities->isEmpty())
                                            class="syllabus-link completable-text accordion-toggle"
                                        @else
                                            class="syllabus-link accordion-toggle"
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
                                                            data-activity-id="{{ $activity->id }}">
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
                                        data-activity-id="{{ $activity->id }}">
                                        - {{ $activity->title }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
            <div class="course-progress-container">
                <h4>Tu Progreso</h4>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-inner" style="width: {{ $progress }}%;">
                        <span>{{ $progress }}%</span>
                    </div>
                </div>
            </div>
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
                    <div class="topic-content" style="margin-bottom: 20px;">
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
                                        <p>📦 Este es un archivo comprimido (<strong>{{ strtoupper($extension) }}</strong>).</p>
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

                        <div class="subtopic-content" style="margin-bottom: 20px;">
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

                    {{-- Paneles de Actividades del Subtema --}}
                    @foreach ($subtopic->activities as $activity)
                        <div class="content-panel" id="content-activity-{{ $activity->id }}">
                            <h3>{{ $activity->title }} ({{ $activity->type }})</h3>

                            {{-- Render específico por tipo --}}
                            @if ($activity->type == 'Cuestionario' && is_array($activity->content))
                                <form action="#" method="POST">
                                    @csrf
                                    <p class="question-text">
                                        {{ $activity->content['question'] ?? '' }}
                                    </p>

                                    @foreach ($activity->content['options'] as $index => $option)
                                        <div class="option-box">
                                            <label>
                                                <input type="radio" name="answer" value="{{ $index }}" {{ Auth::id() == $course->instructor_id ? 'disabled' : '' }}>
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach

                                    @if (Auth::id() != $course->instructor_id)
                                        <button type="submit" class="btn-success">Enviar Respuesta</button>
                                    @else
                                        <p class="instructor-note">(Vista de previsualización para el instructor)</p>
                                    @endif
                                </form>
                            @else
                                <p>{{ is_array($activity->content) ? json_encode($activity->content) : $activity->content }}</p>
                            @endif
                        </div>
                    @endforeach
                @endforeach

                {{-- Paneles de Actividades del Tema --}}
                @foreach ($topic->activities as $activity)
                    <div class="content-panel" id="content-activity-{{ $activity->id }}">
                        <h3>{{ $activity->title }} ({{ $activity->type }})</h3>

                        @if ($activity->type == 'Cuestionario' && is_array($activity->content))
                            <form action="#" method="POST">
                                @csrf
                                <p class="question-text">
                                    {{ $activity->content['question'] ?? '' }}
                                </p>

                                @foreach ($activity->content['options'] as $index => $option)
                                    <div class="option-box">
                                        <label>
                                            <input type="radio" name="answer" value="{{ $index }}" {{ Auth::id() == $course->instructor_id ? 'disabled' : '' }}>
                                            {{ $option }}
                                        </label>
                                    </div>
                                @endforeach

                                @if (Auth::id() != $course->instructor_id)
                                    <button type="submit" class="btn-success">Enviar Respuesta</button>
                                @else
                                    <p class="instructor-note">(Vista de previsualización para el instructor)</p>
                                @endif
                            </form>
                        @else
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
        const links = document.querySelectorAll('.syllabus-link');
        const contentPanels = document.querySelectorAll('.content-panel');
        const syllabusListItems = document.querySelectorAll('.course-syllabus .syllabus-link');

        links.forEach(link => {
            link.addEventListener('click', function () {
                const targetId = this.dataset.target;

                // Ocultar todos los paneles
                contentPanels.forEach(panel => panel.style.display = 'none');

                // Mostrar panel elegido
                const targetPanel = document.querySelector(targetId);
                if (targetPanel) targetPanel.style.display = 'block';
                
                // Resaltar link activo
                syllabusListItems.forEach(item => item.classList.remove('active'));
                this.classList.add('active');
            });
        });

        /* --- NUEVO CÓDIGO (Auto-completar Actividad) --- */

        // 1. Función para enviar el progreso al backend
        const markActivityAsComplete = (activityId, element) => {
            // Prevenir doble envío
            if (element.classList.contains('completed')) {
                return;
            }

            // Usar 'axios' (asegúrate que esté en bootstrap.js)
            axios.post(`/activities/${activityId}/complete`)
                .then(response => {
                    if (response.data.success) {
                        console.log('Actividad completada:', activityId);
                        // Añadir feedback visual
                        element.classList.add('completed');
                    }
                })
                .catch(error => {
                    console.error('Error al completar la actividad:', error);
                });
        };

        // 2. Escuchar clics en los enlaces de actividad
        const activityLinks = document.querySelectorAll('.auto-complete-link');
        
        activityLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                const activityId = this.dataset.activityId;
                if (activityId) {
                    markActivityAsComplete(activityId, this);
                }
            });
        });

        // (Si tus actividades de Cuestionario tienen un botón "Enviar", 
        //  deberás añadir la llamada a 'markActivityAsComplete' 
        //  cuando el usuario responda correctamente.)

        const textLinks = document.querySelectorAll('.completable-text');
        
        textLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Esto solo añade la clase para feedback visual.
                // NO llama al backend y NO afecta la barra de progreso.
                this.classList.add('completed');
            });
        });

        /* --- CÓDIGO DEL ACORDEÓN (CORREGIDO) --- */
        const accordions = document.querySelectorAll('.accordion-toggle');

        accordions.forEach(acc => {
            acc.addEventListener('click', function(event) {
                
                // 1. CAMBIO AQUÍ: Usamos la nueva clase
                this.classList.toggle('accordion-open');
                
                // 2. Obtiene el ID del contenido a mostrar
                const targetId = this.dataset.targetAccordion;
                const content = document.querySelector(targetId);

                // 3. Muestra u oculta el contenido
                if (content) {
                    content.classList.toggle('show');
                }

                // Dejamos que el clic continúe para que el
                // OTRO script (el de los paneles) pueda
                // resaltar el enlace con la clase '.active'
            });
        });
    });
</script>
@endpush
@endsection
