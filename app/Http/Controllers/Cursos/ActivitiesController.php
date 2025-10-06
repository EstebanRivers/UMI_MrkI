<?php

namespace App\Http\Controllers\Cursos;

use App\Models\Cursos\Activities;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class ActivitiesController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'topic_id' => 'nullable|exists:topics,id|required_without_all:subtopic_id',
            'subtopic_id' => 'nullable|exists:subtopics,id|required_without_all:topic_id',
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'content' => 'required|array',
        ]);

        if ($validatedData['type']==='Cuestionario'){
            $request->validate([
                'content.question' => 'required|string',
                'content.options' => 'required|array|min:4',
                'content.options.*' => 'required|string',
                'content.correct_answer' => 'required',

            ]);
        }

        $courseId = null;

        // 2. LÓGICA DE LIMPIEZA DE ID (Asegurar que solo uno se guarde)
        if ($request->filled('subtopic_id')) {
            // Caso 1: Actividad pertenece a un Subtema
            $validatedData['topic_id'] = null; // Forzar a NULL
            
            // Obtener el Course ID para la redirección
            $subtopic = Subtopic::with('topic')->find($request->subtopic_id);
            if (!$subtopic) {
                return redirect()->back()->withErrors(['subtopic' => 'Subtema no encontrado.']);
            }
            $courseId = $subtopic->topic->course_id;

        } elseif ($request->filled('topic_id')) {
            // Caso 2: Actividad pertenece a un Tema
            $validatedData['subtopic_id'] = null; // Forzar a NULL
            
            // Obtener el Course ID para la redirección
            $topic = Topics::find($request->topic_id);
            if (!$topic) {
                return redirect()->back()->withErrors(['topic' => 'Tema no encontrado.']);
            }
            $courseId = $topic->course_id;
        } else {
            // Fallo de seguridad: No se seleccionó nada
            return redirect()->back()->withErrors(['parent' => 'Debe seleccionar un Tema o un Subtema para la actividad.']);
        }

        Activities::create($validatedData);


        return back()->with('success', 'Actividad creada exitosamente.');
    }

    public function destroy(Activities $activity)
    {
        // 1. Elimina la actividad específica
        $activity->delete();

        // 2. Redirige al usuario a la página anterior con un mensaje de éxito
        return back()->with('success', '¡Actividad eliminada exitosamente!');
    }
}