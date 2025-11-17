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
            'course_id' => 'required|exists:courses,id', 
            'is_final_exam' => 'nullable|boolean', 
            
            'topic_id' => 'nullable|exists:topics,id|required_without_all:subtopic_id,is_final_exam',
            'subtopic_id' => 'nullable|exists:subtopics,id|required_without_all:topic_id,is_final_exam',

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
        
        // Lógica para 'SopaDeLetras' o 'Crucigrama'
        elseif ($validatedData['type'] === 'SopaDeLetras') {
            $request->validate([
                'content.words' => 'required|array|min:1', // Debe tener al menos una palabra
                'content.words.*' => 'required|string|distinct', // Palabras deben ser únicas
                'content.grid_size' => 'required|integer|min:5|max:20', // Tamaño de 5x5 a 20x20
            ]);
        }

        elseif ($validatedData['type'] === 'Crucigrama') {
            $request->validate([
                'content.grid_size' => 'required|integer|min:5|max:25',
                
                // Validar pistas horizontales (si existen)
                'content.clues.across' => 'nullable|array',
                'content.clues.across.*.number' => 'required|integer',
                'content.clues.across.*.clue' => 'required|string',
                'content.clues.across.*.answer' => 'required|string',
                'content.clues.across.*.x' => 'required|integer', // Coordenada X (columna)
                'content.clues.across.*.y' => 'required|integer', // Coordenada Y (fila)

                // Validar pistas verticales (si existen)
                'content.clues.down' => 'nullable|array',
                'content.clues.down.*.number' => 'required|integer',
                'content.clues.down.*.clue' => 'required|string',
                'content.clues.down.*.answer' => 'required|string',
                'content.clues.down.*.x' => 'required|integer',
                'content.clues.down.*.y' => 'required|integer',
            ]);
        }

        $courseId = null;
        $validatedData['is_final_exam'] = $request->has('is_final_exam');

        // 2. LÓGICA DE LIMPIEZA DE ID (Asegurar que solo uno se guarde)
        if ($validatedData['is_final_exam']) {
            // Es un examen final, pertenece al CURSO. Anular temas.
            $validatedData['topic_id'] = null;
            $validatedData['subtopic_id'] = null;

        } elseif ($request->filled('subtopic_id')) {
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