<?php
namespace App\Http\Controllers\Cursos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompletionController extends Controller
{
    public function mark(Request $request)
    {
        $user = Auth::user();

        // Validamos el 'type'
        $type = $request->input('type'); // 'Topics', 'Subtopic', 'Activities'
        if (!in_array($type, ['Topics', 'Subtopic', 'Activities'])) {
            return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
        }

        // Construimos el nombre completo del modelo
        $modelClass = 'App\\Models\\Cursos\\' . $type;
        $id = $request->input('id');

        // Usamos firstOrCreate para crear el registro solo si no existe
        $completion = $user->completions()->firstOrCreate([
            'completable_type' => $modelClass,
            'completable_id'   => $id
        ]);

            if ($completion->wasRecentlyCreated) {
            // Necesitamos encontrar el Curso ID basado en lo que se completó
        
            $courseId = null;
            if ($request->type === 'Topics') {
                $item = \App\Models\Cursos\Topics::find($request->id);
                $courseId = $item->course_id;
            } elseif ($request->type === 'Subtopic') {
                $item = \App\Models\Cursos\Subtopic::find($request->id);
                $courseId = $item->topic->course_id;
            }
            
            if ($courseId) {
                $course = \App\Models\Cursos\Course::find($courseId);
                $course->calculateUserProgress($user->id);
            }
        }

        return response()->json(['success' => true]);
    }
}