<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class MateriaController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'materias'; // Definido explícitamente para esta vista

        // 1. Columnas a seleccionar del modelo Materia
        $materiaColums = [
            'id',
            'nombre',
            'creditos',
            'type',
            'semestre',
            'career_id', // ¡IMPORTANTE! Clave foránea para la relación
        ];

        // 2. Columnas a seleccionar del modelo Career
        $careerColums = [
            'id', // ¡IMPORTANTE! Clave primaria para la relación
            'name'
        ];
        
        // 3. Ejecución de la consulta
        $dataList = Materia::query()
            ->select($materiaColums)
            
            // Cargar la relación 'career' con columnas específicas
            ->with(['career' => function (Relation $query) use ($careerColums) {
                $query->select($careerColums);
            }])
            ->get();

        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }
    
}
