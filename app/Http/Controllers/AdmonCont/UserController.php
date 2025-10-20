<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\Relations\Relation;

// Modelos necesarios para la consulta y relaciones
use App\Models\Users\User; // Ajusta este namespace si tu modelo User no está directamente en App\Models
use App\Models\Users\AcademicProfile; // Necesario para la relación que vamos a cargar
use App\Models\AdmonCont\Materia; 
use App\Models\AdmonCont\Career; 
// El modelo Role no es estrictamente necesario aquí si solo lo usamos en whereHas, pero no estorba. 
// Para mayor limpieza, lo omitimos si ya está importado en el modelo User.


class UserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): View
    {
        $routeName = $request->route()->getName();
        $listType = match ($routeName) {
        'Listas.students.index' => 'students',
        'Listas.members.index' => 'members',
        'Listas.users.index' => 'users',
        'Listas.materias.index' => 'materias',
        default => null, // Si la ruta no coincide con nada
    };

    if (is_null($listType)) {
        abort(404); // Detener si la ruta no está definida en la lista
    }

    // Inicializamos la variable que contendrá la lista de resultados
    $dataList = null;

    if($listType === 'students' || $listType === 'members'){

        // Determinar el rol a filtrar
        $roleName = ($listType === 'students') ? 'estudiante' : 'docente';
            
        // 1. Columnas a seleccionar de la tabla 'users'
        $userColumns = [
            'id',
            'nombre', 
            'apellido_paterno', 
            'apellido_materno'
        ];
        // 2. Columnas a seleccionar de la tabla 'datos_academicos' (¡incluye user_id!)
        // Nota: Aquí se seleccionan todos los campos relevantes, Laravel los manejará.
        $academicColumns = [
            'user_id',
            'status', 
            'carrera'
        ];
        $dataList = User::query()
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName); 
            })
            ->select($userColumns)
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->get();
    }elseif ($listType === 'materias') {
    
        // NOTA: $roleName no se usa aquí. Puedes omitir o eliminar la línea: $roleName = ($listType === 'materias');

        // 1. Columnas a seleccionar del modelo Materia
        $materiaColums = [
            'id',
            'nombre',
            'creditos',
            'type',
            'semestre',
            'career_id', // <-- ¡IMPORTANTE! Incluye la clave foránea para la relación
        ];

        // 2. Columnas a seleccionar del modelo Career (asumo que se relaciona con Materia)
        $careerColums = [
            'id', // <-- ¡IMPORTANTE! Incluye la clave primaria para la relación
            'name'
        ];
        
        // 3. Ejecución de la consulta
        $dataList = Materia::query()
            ->select($materiaColums)
            
            // Cargar la relación 'career' con columnas específicas
            // ¡Usamos $careerColums para la clausura, NO $materiaColums!
            ->with(['career' => function (Relation $query) use ($careerColums) {
                $query->select($careerColums);
            }])
            
            ->get(); // <-- ¡CRUCIAL! Ejecutar la consulta para obtener los datos
    }

    $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
    
    return view($viewPath, [
        
        'dataList' => $dataList,
    ]);
        
    }
}
