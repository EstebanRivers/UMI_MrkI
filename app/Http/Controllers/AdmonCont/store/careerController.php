<?php

namespace App\Http\Controllers\AdmonCont\store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//Modelos
use App\Models\AdmonCont\Career;

class careerController extends Controller
{
    public function store(Request $request){
        //Validación de datos
        //1.Nombre de input
        //2.especificar que si es requerida
        //3.tipo de dato
        //4.restriccion de datos(tamaño maximo,tamaño minimo, valor especifico,etc.)
        $request->validate([
            //1.       2.       3.     4.
            'name' => 'required|string|max:255',
            'official_id' => 'required|string|max:255',
            'type' => 'required|in:Presencial,En linea',
            'semesters' => 'required|integer|min:1|max:255',
            

        ]);
        //LLamada para guardar datos
        //1.Nombre de columna de tabla
        //2.Llamada de request
        //3.Nombre de valor
        Career::create([
            //1.      2.        3.
            'name' => $request->name,
            'official_id' => $request->official_id,
            'description1' => $request->description1,
            'description2' => $request->description2,
            'description3' => $request->description3,
            'type' => $request->type,
            'semesters' => $request->semesters,
        ]);
        //retorno a pantalla

        return redirect()->route('Carreras.index')->with('success', 'Carrera creada exitosamente.');
    }
    //Eliminar
    //1. Modulo a usar
    //2. Clase(debe ser igual al nombre de la tabla pero en singular)
    //                      1.     2.
    public function destroy(Career $carrera) 
    {
        // Laravel automáticamente encuentra la carrera por la ID pasada en la ruta
        // y la elimina.
        $carrera->delete(); 
        
        // 2. Redirigir de vuelta a la lista con un mensaje de éxito
        return redirect()->route('Carreras.index')->with('success', 'Carrera eliminada exitosamente.');
    }
}
