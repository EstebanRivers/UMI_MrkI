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
            'nombre', 
            'apellido_paterno', 
            'apellido_materno',
        ];
        // 2. Columnas a seleccionar de la tabla 'datos_academicos' (¡incluye user_id!)
        // Nota: Aquí se seleccionan todos los campos relevantes, Laravel los manejará.
        $academicColumns = [
            'user_id', 
            'carrera', 
            'departamento',
        ];
        $dataList = User::query()
                // Filtrar usuarios que tienen el rol específico
                ->whereHas('roles', function (Builder $query) use ($roleName) {
                    $query->where('name', $roleName);
                })
                // Seleccionar solo las columnas necesarias de la tabla 'users'
                ->select($userColumns)
                // Cargar la relación 'datosAdicionales' con columnas específicas
                ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                    $query->select($academicColumns);
                }])
                ->get();
    }

    $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
    
    return view($viewPath, [
        
        'dataList' => $dataList,
    ]);
        
    }
}
