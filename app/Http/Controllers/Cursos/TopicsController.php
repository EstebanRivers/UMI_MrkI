<?php

namespace App\Http\Controllers\Cursos;

use Illuminate\Http\Request;
use App\Models\Cursos\Course;
use App\Models\Cursos\Topics;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class TopicsController extends Controller
{
    /**
     * Mostrar formulario de creación de temas para un curso específico
     */
    public function create(Course $course): View
    {
        $course->load('topics.activities');
        $formActions = route('topics.store');
        return view('layouts.Cursos.topic.create', [
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
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,pptx,mp4,mov,avi,wmv|max:163840', // max 160MB
        ]);

        if ($request->hasFile('file')){
            $path = $request->file('file')->store('topic_files', 'public');

            $validatedData['file_path']=$path;
        }

        Topics::create($validatedData);

        return back()->with('success', 'Tema creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un tema existente.
     */
    public function edit(Topics $topic)
    {
        return response()->json([
            'id' => $topic->id,
            'title' => $topic->title,
            'description' => $topic->description,
            'file_path' => $topic->file_path,
            'course_id' => $topic->course_id
        ]);
    }

    /**
     * Actualiza el tema en la base de datos.
     */
    public function update(Request $request, Topics $topic)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255', 
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,mp4,mov,avi|max:163840', // max 160MB
        ]);

        // Actualizar tema
        $topic->title = $request->title;
        $topic->description = $request->description;

        // Manejar archivo si se subió uno nuevo
        if ($request->hasFile('file')) {
            // Eliminar archivo anterior si existe
            if ($topic->file_path) {
                Storage::disk('public')->delete($topic->file_path);

            }
            $topic->file_path = $request->file('file')->store('topics', 'public');
        }

        $topic->save();

        return redirect()->back()->with('success', 'Tema actualizado correctamente.');
    }

    public function destroy(Topics $topic)
    {
        // Gracias al Route Model Binding, Laravel nos encuentra el tema
        $topic->delete();

        // Redirigimos a la página anterior
        return back()->with('success', '¡Tema eliminado exitosamente!');
    }
}
    
