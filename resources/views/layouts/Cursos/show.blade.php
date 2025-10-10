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
                    <strong class="syllabus-link" data-target="#content-topic-{{ $topic->id }}">
                        {{ $topic->title }}
                    </strong>

                    {{-- Actividades del tema --}}
                    @if($topic->activities->count() > 0)
                        <ul>
                            @foreach ($topic->activities as $activity)
                                <li class="syllabus-link" data-target="#content-activity-{{ $activity->id }}">
                                    - {{ $activity->title }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    {{-- Subtemas --}}
                    @if($topic->subtopics->count() > 0)
                        <ul>
                            @foreach ($topic->subtopics as $subtopic)
                                <li>
                                    <span class="syllabus-link" data-target="#content-subtopic-{{ $subtopic->id }}">
                                        ▸ {{ $subtopic->title }}
                                    </span>

                                    {{-- Actividades del subtema --}}
                                    @if($subtopic->activities->count() > 0)
                                        <ul>
                                            @foreach ($subtopic->activities as $activity)
                                                <li class="syllabus-link" data-target="#content-activity-{{ $activity->id }}">
                                                    - {{ $activity->title }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
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
                                <a href="{{ asset('storage/' . $topic->file_path) }}" target="_blank" class="download-link">
                                    📎 Descargar Material ({{ strtoupper($extension) }})
                                </a>
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
    });
</script>
@endpush
@endsection
