<?php

namespace App\Http\Controllers\Cursos;

use App\Models\Cursos\Activities;
use App\Models\Cursos\Subtopic;
use App\Models\Cursos\Topics;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse; 
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ActivitiesController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'topic_id' => 'nullable|exists:topics,id|required_without_all:subtopic_id',
            'subtopic_id' => 'nullable|exists:subtopics,id|required_without_all:topic_id',
            'title' => 'required|string|max:255',
            'type' => 'required|string', // Tipos: 'Cuestionario', 'SopaDeLetras', 'Crucigrama'
            'content' => 'required|array',
        ]);

        if ($validatedData['type']==='Cuestionario'){
            $request->validate([
                'content.question' => 'required|string',
                'content.options' => 'required|array|min:4', // Ajusta si quieres menos opciones
                'content.options.*' => 'required|string',
                'content.correct_answer' => 'required', // Debería ser el índice (0, 1, 2, 3...)

            ]);
        }
        
        // Lógica para 'SopaDeLetras' o 'Crucigrama'
        // if ($validatedData['type']==='SopaDeLetras'){
        //     $request->validate(['content.words' => 'required|array']);
        // }

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

    /**
     * Procesa el envío de una actividad interactiva (Cuestionario, Sopa, etc.)
     */
    public function submit(Request $request, Activities $activity): JsonResponse
    {
        $user = Auth::user();

        // 1. Validar la respuesta (si aplica)
        if ($activity->type === 'Cuestionario') {
            $validated = $request->validate(['answer' => 'required']);
            
            $userAnswer = $validated['answer'];
            $correctAnswer = $activity->content['correct_answer'] ?? null;

            // Comparamos la respuesta enviada (ej. "1") con la correcta (ej. "1")
            if (strval($userAnswer) !== strval($correctAnswer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Respuesta incorrecta. ¡Inténtalo de nuevo!'
                ], 422); // Error de validación
            }
        }

        // Para 'SopaDeLetras' o 'Crucigrama', la validación se hace en el frontend (JS).
        // El simple hecho de llamar a esta ruta significa que el usuario completó el juego.
        // Si quisieras más seguridad, el JS debería enviar una "prueba" que el backend pueda validar.
        // Por ahora, confiamos en que si el JS lo envía, está completo.

        // 2. Marcar como completado usando el sistema polimórfico
        $completion = $user->completions()->firstOrCreate([
            'completable_type' => Activities::class, // Usar el FQCN del modelo
            'completable_id'   => $activity->id
        ]);

        return response()->json([
            'success' => true,
            'created' => $completion->wasRecentlyCreated, // Para que el JS sepa si debe actualizar la barra
            'message' => '¡Actividad completada exitosamente!'
        ]);
    }

}