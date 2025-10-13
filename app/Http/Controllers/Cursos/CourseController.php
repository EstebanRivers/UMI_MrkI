<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreCourseRequest; 
use App\Models\Cursos\Course;
use App\Models\Users\Institution;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;


class CourseController extends Controller
{
    use AuthorizesRequests;
    /**
     * Muestrar lista de cursos
     */
    public function index(): View
    {
        $activeInstitutionId = session('active_institution_id');
        $activeRoleName = session('active_role_name');

        // CORREGIDO: Usar la variable correcta y filtrada
        $course = Course::with('instructor', 'institution')
            ->where('institution_id', $activeInstitutionId)
            ->latest()
            ->get();

        // Para roles específicos, mostrar información adicional
        $canManageCourses = in_array($activeRoleName, ['master', 'docente']);

        return view('layouts.Cursos.index', compact('course', 'canManageCourses'));
    }

    /**
     * Mostrar formulario de creación de curso
     */
    public function create(): View
    {
         // Obtenemos el ID de la institución de la sesión actual del usuario
        $institutionId = session('active_institution_id');

        // Cargamos la institución actual con sus relaciones (carreras, departamentos, etc.)
        $currentInstitution = Institution::with(['careers', 'departments.workstations'])->find($institutionId);

        // Pasamos solo la institución actual a la vista.
        return view('layouts.Cursos.create', compact('currentInstitution'));
    }

    /**
     * Guardar un nuevo curso
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');
        $activeRoleName = session('active_role_name');

        // VALIDACIÓN DE SEGURIDAD: Verificar que la institución proporcionada coincida con la activa
        $validatedData = $request->validated();

        // SEGURIDAD: Forzar que el curso se cree en la institución activa
        if ($validatedData['institution_id'] != $activeInstitutionId) {
            Log::warning('Intento de crear curso en institución no autorizada', [
                'user_id' => Auth::id(),
                'active_institution_id' => $activeInstitutionId,
                'attempted_institution_id' => $validatedData['institution_id'],
                'ip' => $request->ip()
            ]);

            return redirect()->back()->withErrors([
                'institution_id' => 'No tienes autorización para crear cursos en esa institución.'
            ])->withInput();
        }

        $courseData = $validatedData;
        $courseData['instructor_id'] = Auth::id();

        // Manejo de imagen
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('courses', 'public');
            $courseData['image'] = $path;
        }

        $course = Course::create($courseData);

        Log::info('Curso creado exitosamente', [
            'course_id' => $course->id,
            'instructor_id' => Auth::id(),
            'institution_id' => $activeInstitutionId,
            'role' => $activeRoleName
        ]);

        return redirect()->route('course.topic.create', ['course' => $course->id])
            ->with('success', 'Curso creado exitosamente.');
    }

    /**
     * Mostrar detalles de un curso
     */
    public function show(Course $course): View
    {
        // Cargar relaciones necesarias
        $course->load(['topics.subtopics.activities', 'instructor', 'institution']);

        return view('layouts.Cursos.show', compact('course'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Course $course): View
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de editar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes editar cursos de otra institución.');
        }

        // Autorización adicional: Verificar que el usuario es el instructor o master
        $this->authorize('update', $course);

        return view('layouts.Cursos.edit', compact('course'));
    }

    /**
     * Actualizar curso
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de actualizar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes actualizar cursos de otra institución.');
        }

        // Autorización: Policy
        $this->authorize('update', $course);

        // Validación
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:0|max:100',
            'hours' => 'required|integer|min:0|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Manejo de imagen
        if ($request->hasFile('image')) {
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }
            $validatedData['image'] = $request->file('image')->store('courses', 'public');
        }

        $course->update($validatedData);

        Log::info('Curso actualizado', [
            'course_id' => $course->id,
            'user_id' => Auth::id()
        ]);

        // Redirigir según la acción solicitada
        if ($request->input('action') == 'save_and_continue') {
            return redirect()->route('course.topic.create', ['course' => $course->id])
                ->with('success', 'Curso actualizado. Ahora puedes editar sus temas.');
        }

        return redirect()->route('Cursos.index')
            ->with('success', 'Curso actualizado exitosamente.');
    }

    /**
     * Eliminar curso
     */
    public function destroy(Course $course): RedirectResponse
    {
        $activeInstitutionId = session('active_institution_id');

        // VALIDACIÓN DE SEGURIDAD: Verificar institución
        if ($course->institution_id != $activeInstitutionId) {
            Log::warning('Intento de eliminar curso de otra institución', [
                'user_id' => Auth::id(),
                'course_id' => $course->id,
                'course_institution_id' => $course->institution_id,
                'user_active_institution_id' => $activeInstitutionId
            ]);

            abort(403, 'No puedes eliminar cursos de otra institución.');
        }

        // Autorización: Policy
        $this->authorize('delete', $course);

        // Eliminar imagen asociada
        if ($course->image) {
            Storage::disk('public')->delete($course->image);
        }

        $courseTitle = $course->title;
        $course->delete();

        Log::info('Curso eliminado', [
            'course_id' => $course->id,
            'course_title' => $courseTitle,
            'user_id' => Auth::id(),
            'institution_id' => $activeInstitutionId
        ]);

        return redirect()->route('Cursos.index')
            ->with('success', 'Curso "' . $courseTitle . '" eliminado exitosamente.');
    }
}