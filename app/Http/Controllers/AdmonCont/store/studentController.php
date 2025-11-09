<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class studentController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'students'; // Definimos el tipo de lista fijo
        $roleName = 'estudiante'; // Definimos el rol fijo

        // --- Definición de Columnas ---
        
        // 1. Columnas a seleccionar de la tabla 'users'
        $userColumns = [
            'id',
            'nombre', 
            'apellido_paterno', 
            'apellido_materno',
            'created_at',
        ];
        
        // 2. Columnas a seleccionar de la tabla 'datos_academicos' (¡incluye user_id!)
        $academicColumns = [
            'user_id', // ¡CRUCIAL para la relación!
            'status', 
            'carrera_id'
        ];

        $careerColumns=[
            'official_id',
            'name',
        ];
        
        // --- Ejecución de la Consulta ---
        
        $dataList = User::query()
            // Filtra usuarios que tienen el rol 'estudiante'
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName); 
            })
            // Selecciona las columnas necesarias de la tabla 'users'
            ->select($userColumns)
            // Carga la relación 'academicProfile' con columnas específicas
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->get(); // Ejecuta la consulta y obtiene la colección de resultados

        // --- Devolución de la Vista ---
        
        // La ruta de la vista ahora es fija para estudiantes
        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }
}
