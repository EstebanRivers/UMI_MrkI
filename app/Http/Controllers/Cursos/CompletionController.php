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

        // 1. Validamos y guardamos en variables limpias
        $type = $request->input('type'); 
        $id   = $request->input('id');

        if (!in_array($type, ['Topics', 'Subtopic', 'Activities'])) {
            return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
        }

        $modelClass = 'App\\Models\\Cursos\\' . $type;

        // 2. Crear registro
        $completion = $user->completions()->firstOrCreate([
            'completable_type' => $modelClass,
            'completable_id'   => $id
        ]);

        // 3. Si es nuevo, calcular progreso
        if ($completion->wasRecentlyCreated) {
            
            $courseId = null;
            
            // Usamos la variable $type y $id que ya definimos arriba (más limpio)
            if ($type === 'Topics') {
                $item = \App\Models\Cursos\Topics::find($id);
                if ($item) $courseId = $item->course_id; // <--- Agregamos protección if($item)

            } elseif ($type === 'Subtopic') {
                $item = \App\Models\Cursos\Subtopic::find($id);
                if ($item) $courseId = $item->topic->course_id; // <--- Agregamos protección if($item)

            } elseif ($type === 'Activities') {
                $item = \App\Models\Cursos\Activities::find($id);
                if ($item) $courseId = $item->course_id;
            }
            
            // Recalcular solo si encontramos el curso
            if ($courseId) {
                $course = \App\Models\Cursos\Course::find($courseId);
                if ($course) $course->calculateUserProgress($user->id);
            }
        }

        return response()->json([
            'success' => true, 
            'created' => $completion->wasRecentlyCreated
        ]);
    }
}