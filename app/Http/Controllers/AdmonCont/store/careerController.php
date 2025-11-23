<?php

namespace App\Http\Controllers\AdmonCont\store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//Modelos
use App\Models\Users\Career;

class careerController extends Controller
{
    public function create()
    {
        return view('layouts.ControlAdmin.Carreras.create'); // Aquí se llama/renderiza create.blade.php
    }
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

    public function update(Request $request, Career $career)
    {
        // 1. VALIDACIÓN
        // La validación verifica que los campos sean válidos. 
        // Importante: La regla 'unique' debe ignorar el ID de la carrera actual ($career->id).
        $request->validate([
            'name' => 'required|string|max:255|unique:carrers,name,' . $career->id,
            'official_id' => 'required|string|max:100',
            'type' => 'required|in:Presencial,En linea',
            'semesters' => 'required|integer|min:1|max:15',
            'description1' => 'nullable|string|max:500', 
            'description2' => 'nullable|string|max:500', 
            'description3' => 'nullable|string|max:500',
        ]);
        
        // Si la validación falla, Laravel automáticamente redirige de vuelta a la URL anterior 
        // y adjunta los errores ($errors) y los datos viejos (old()), lo que activa tu modal.

        // 2. ACTUALIZACIÓN DEL MODELO
        $career->update($request->all());

        // 3. REDIRECCIÓN TRAS ÉXITO
        // Redirige al listado principal con un mensaje de éxito.
        return redirect()->route('Carreras.index')->with('success', '¡Carrera actualizada exitosamente!');
    }
    //Eliminar
    //1. Modulo a usar
    //2. Clase(debe ser igual al nombre de la tabla pero en singular)
    //                      1.     2.
    public function destroy(career $carrera) 
    {
        // Laravel automáticamente encuentra la carrera por la ID pasada en la ruta
        // y la elimina.
        $carrera->delete(); 
        
        // 2. Redirigir de vuelta a la lista con un mensaje de éxito
        return redirect()->route('Carreras.index')->with('success', 'Carrera eliminada exitosamente.');
    }
}
