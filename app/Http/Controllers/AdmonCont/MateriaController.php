<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\AdmonCont\Materia;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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

        $carreras = Career::all(['id', 'name']);
        
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
            'dataList' => $dataList,'carreras' => $carreras
        ]);
    }

    // MateriaController.php

    public function store(Request $request)
    {
        // 1. VALIDACIÓN DE DATOS
        $validatedData = $request->validate([
            // ¡CORRECCIÓN AQUÍ! Se usa 'carrers' como nombre de la tabla
            'carrera_id' => ['required', 'integer', 'exists:carrers,id'], 
            'nombre' => ['required', 'string', 'max:100'],
            'creditos' => ['required', 'integer', 'min:1'],
            'semestre' => ['required', 'integer', 'min:1', 'max:15'], 
            'type' => ['required', 'in:Presencial,En linea'], 
        ]);
        
        // 2. RENOMBRAR Y PREPARAR DATOS
        // Mapeamos los nombres del formulario a los nombres de las columnas en la DB
        $dataToSave = [
            'career_id' => $validatedData['carrera_id'], // Mapeo de input 'carrera_id' a DB 'career_id'
            'nombre' => $validatedData['nombre'],
            'creditos' => $validatedData['creditos'],
            'semestre' => $validatedData['semestre'],
            'type' => $validatedData['type'],
        ];
        
        // 3. CREACIÓN DEL REGISTRO
        // Asegúrate de que el modelo Materia tenga 'career_id' en $fillable
        \App\Models\AdmonCont\Materia::create($dataToSave); 

        // 4. REDIRECCIÓN
        return \Illuminate\Support\Facades\Redirect::route('Listas.materias.index') 
            ->with('success', '¡Materia creada exitosamente!');
    }
    public function update(Request $request, Materia $registro)
    {
        // 1. VALIDACIÓN
        $validatedData = $request->validate([
            'carrera_id' => ['required', 'integer', 'exists:carrers,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'creditos' => ['required', 'integer', 'min:1'],
            'semestre' => ['required', 'integer', 'min:1', 'max:15'], 
            'type' => ['required', 'in:Presencial,En linea'], 
            'descripcion' => ['nullable', 'string', 'max:500'], // Validamos la descripción
        ]);
        
        // 2. PRESERVAR LA CLAVE (¡USANDO $registro!)
        // Añadimos la clave actual al array de datos validados
        // Esto asume que 'clave' no se está actualizando desde este formulario.
        $validatedData['clave'] = $registro->clave ?? null;

        // 3. ACTUALIZACIÓN (Directo y limpio)
        $registro->update($validatedData); 

        // 4. REDIRECCIÓN
        return Redirect::route('Listas.materias.index') 
            ->with('success', '¡Materia actualizada exitosamente!');
    }
    
}
