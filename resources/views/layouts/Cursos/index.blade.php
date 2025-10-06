@extends('layouts.app')

@section('title', 'Cursos - UMI')

@vite(['resources/css/courses.css', 'resources/js/app.js'])

@section('content')
<div class="courses-wrapper">
    <!-- Header -->
    <div class="courses-header">
        <div>
            <h1 class="courses-title">Cursos Disponibles</h1>
            <p class="courses-subtitle">
                @if(Auth::user()->hasAnyRole(['master', 'docente']))
                    Gestiona y crea cursos para los estudiantes
                @else
                    Explora y inscríbete a los cursos disponibles
                @endif
            </p>
        </div>
        
        @if(Auth::user()->hasAnyRole(['master', 'docente']))
            <button onclick="window.navigateTo('{{ route('courses.create') }}')" class="btn-create">
                + Crear Curso
            </button>
        @endif
    </div>

    <!-- Filtros rápidos -->
    {{-- <div class="courses-filters">
        <button class="filter-btn active" data-filter="all">Todos</button>
        <button class="filter-btn" data-filter="basico">Básico</button>
        <button class="filter-btn" data-filter="intermedio">Intermedio</button>
        <button class="filter-btn" data-filter="avanzado">Avanzado</button>
        @if(Auth::user()->hasAnyRole(['alumno', 'anfitrion']))
            <button class="filter-btn available" data-filter="available">Disponibles para mí</button>
            <button class="filter-btn enrolled" data-filter="enrolled">Mis Cursos</button>
        @endif
    </div> --}}

    <!-- Grid de cursos -->
    <div class="courses-container">
        @forelse ($course as $courses)
            <div class="course-card">
                <img src="{{ asset('storage/' . $courses->image) }}" alt="Imagen del curso">
                <div class="course-info">
                    <h3 class="course-title">{{ $courses->title }}</h3>
                    <p class="course-description">{{ $courses->description }}</p>
                    <div class="course-meta">
                        <span>Créditos: {{ $courses->credits }}</span>
                        <span>Horas: {{ $courses->hours }}</span>
                    </div>

                    <div class="btn-display">
                        <button type="submit" class="btn-view">
                            <a href="{{ route('course.show', $courses)}}">
                                <img src="{{asset('icons/eye-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy">
                            </a>
                        </button>
                        @can('update', $courses)
                            <button type="submit" class="btn-edit">
                                <a href="{{ route('courses.edit', $courses) }}">
                                <img src="{{asset('icons/pen-to-square-solid-full.svg')}}" alt="" style="width:27;height:27px" loading="lazy">
                                </a>
                            </button>
                        @endcan
                        {{-- NUEVO FORMULARIO PARA ELIMINAR --}}
                        @can('delete', $courses)
                            <form action="{{ route('courses.destroy', $courses) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este curso?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete">
                                    <img src="{{asset('icons/Vector.svg')}}" alt="" style="width:38;height:25px" loading="lazy">
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="no-courses-message">
                <p>Aún no hay cursos disponibles. ¡Vuelve pronto!</p>
            </div>
        @endforelse
    </div>
</div>


<script>
// Filtros
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const courseCards = document.querySelectorAll('.course-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Actualizar botones activos
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            
            courseCards.forEach(card => {
                let show = false;
                
                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case 'basico':
                    case 'intermedio':
                    case 'avanzado':
                        show = card.dataset.difficulty === filter;
                        break;
                    case 'available':
                        show = card.dataset.available === 'true';
                        break;
                    case 'enrolled':
                        show = card.dataset.enrolled === 'true';
                        break;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        });
    });
});

// Inscripción a cursos
function enrollInCourse(courseId) {
    if (!confirm('¿Estás seguro de que quieres inscribirte a este curso?')) {
        return;
    }
    
    fetch(`/courses/${courseId}/enroll`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al inscribirse al curso');
    });
}

// Desinscripción de cursos
function unenrollFromCourse(courseId) {
    if (!confirm('¿Estás seguro de que quieres desinscribirte de este curso?')) {
        return;
    }
    
    fetch(`/courses/${courseId}/unenroll`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al desinscribirse del curso');
    });
}
</script>
@endsection