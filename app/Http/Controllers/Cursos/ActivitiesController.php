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
use App\Models\Cursos\Completion;
use Illuminate\Validation\Rule;

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

        elseif ($validatedData['type'] === 'Examen') {
            $request->validate([
                'content.questions' => 'required|array|min:1',
                'content.questions.*.question' => 'required|string',
                'content.questions.*.options' => 'required|array|min:2',
                'content.questions.*.options.*' => 'required|string',
                'content.questions.*.correct_answer' => 'required|string',
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

        $courseId = $validatedData['course_id'];
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
        $score = 0; 
        $message = '¡Actividad completada!';

        // 1. Validar "Cuestionario" (1 pregunta)
        if ($activity->type === 'Cuestionario') {
            $validated = $request->validate(['answer' => 'required']);
            $userAnswer = $validated['answer'];
            $correctAnswer = $activity->content['correct_answer'] ?? null;

            if (strval($userAnswer) !== strval($correctAnswer)) {
                return response()->json(['success' => false, 'message' => 'Respuesta incorrecta.'], 422);
            }
            $score = 100.00; // Si es correcta, 100
        }

        // 2. Validar "Examen" (múltiples preguntas)
        elseif ($activity->type === 'Examen') {
            $userAnswersData = $request->validate(['answers' => 'required|array']);
            $userAnswers = $userAnswersData['answers'];
            
            $questions = $activity->content['questions'] ?? [];
            $totalQuestions = count($questions);
            $correctCount = 0;

            if ($totalQuestions > 0) {
                foreach ($userAnswers as $index => $answerData) {
                    $questionIndex = $answerData['q'];
                    $userAnswerIndex = $answerData['a'];

                    if (isset($questions[$questionIndex])) {
                        $correctAnswerIndex = $questions[$questionIndex]['correct_answer'] ?? null;
                        if (strval($userAnswerIndex) === strval($correctAnswerIndex)) $correctCount++;
                    }
                }
                $score = round(($correctCount / $totalQuestions) * 100, 2);
                $message = "¡Examen completado! Tu calificación: $correctCount / $totalQuestions ($score%)";
            } else {
                return response()->json(['success' => false, 'message' => 'Este examen no tiene preguntas.'], 422);
            }
        }

        elseif ($activity->type === 'Crucigrama' ) {
            // $validated = $request->validate(['completed' => 'required|boolean']);
            // $completed = $validated['completed'];

            // if (!$completed) {
            //     return response()->json(['success' => false, 'message' => 'El juego no se completó correctamente.'], 422);
            // }
            $score = 100.00; // Completado correctamente
        }

        // 2. Marcar como completado usando el sistema polimórfico
        $completion = $user->completions()->updateOrCreate(
            [
                'completable_type' => Activities::class, // Usar el FQCN del modelo
                'completable_id'   => $activity->id
            ],

            [
                'score' => $score
            ]
        );

        return response()->json([
            'success' => true,
            'created' => $completion->wasRecentlyCreated, // Para que el JS sepa si debe actualizar la barra
            'score'   => $score,
            'message' => '¡Actividad completada exitosamente!'
        ]);
    }

}