<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\Topics;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

class TopicsController extends Controller
{
    /**
     * Mostrar formulario de creación de temas para un curso específico
     */
    public function create(Course $course): View
    {
        $course->load('topics.activities');
        $formActions = route('topics.store');
        return view('course.topic.create', [
            'course' => $course, 
            'formActions' => $formActions
        ]);
    }

    /**
     * Guardar un nuevo tema
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,pptx,mp4,mov,avi,wmv|max:51200',
        ]);

        if ($request->hasFile('file')){
            $path = $request->file('file')->store('topic_files', 'public');

            $validatedData['file_path']=$path;
        }

        Topics::create($validatedData);

        return back()->with('success', 'Tema creado exitosamente.');
    }

    public function destroy(Topics $topic)
    {
        // Gracias al Route Model Binding, Laravel nos encuentra el tema
        $topic->delete();

        // Redirigimos a la página anterior
        return back()->with('success', '¡Tema eliminado exitosamente!');
    }
}
    
