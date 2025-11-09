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
        // 1. Configuración para estudiantes
        $roleName = 'estudiante';
        $listType = 'students'; // Definimos el tipo de lista directamente

        // 2. Columnas a seleccionar de la tabla 'users'
        $userColumns = [
            'id',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'created_at',
        ];

        // 3. Columnas a seleccionar de la relación 'academicProfile' (datos_academicos)
        $academicColumns = [
            'user_id', // ¡CRUCIAL! Debe incluirse la clave foránea
            'status',
            'carrera_id',
        ];

        // 4. Ejecución de la consulta
        $dataList = User::query()
            // Filtra por el rol 'estudiante' usando whereHas
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName);
            })
            // Selecciona las columnas de la tabla 'users'
            ->select($userColumns)
            // Carga la relación 'academicProfile' con columnas específicas
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->get(); // Ejecutar la consulta

        // 5. Devolver la vista
        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';

        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }
}
