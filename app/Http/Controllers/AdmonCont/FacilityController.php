<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class FacilityController extends Controller
{
    //Clase Index
    public function index(){
        $data = Facility::all();

        return view('layouts.ControlAdmin.Infraestrucuta.index', compact('data'));
    }
    public function createForm(){
        return view('layouts.ControlAdmin.Infraestrucuta.components.create');
    }
    public function store(Request $request){
        // 1. Validación de los datos
        $request->validate([
            // string(10) | Debe ser requerido
            'numero_aula' => 'required|string|max:10', 
            // string(255) | Debe ser requerido
            'seccion' => 'required|string|max:255', 
            // integer | Es nullable en BD, pero si se envía debe ser entero
            'capacidad' => 'nullable|integer|min:0', 
            // string(100) | Es nullable en BD
            'ubicacion' => 'nullable|string|max:100', 
            // string(20) | Debe ser requerido y solo permitir los valores esperados
            'tipo' => 'required|string|in:Aula,Laboratorio,Otro', 
        ]);

        // 2. Creación del modelo (Ejemplo - Asumiendo que has definido $fillable en el modelo Facility)
        $facility = Facility::create($request->all());

        // 3. Devolver una respuesta JSON de éxito (Axios lo espera)
        return response()->json([
            'message' => 'Aula creada exitosamente.',
            'data' => $facility, // Opcional: devolver los datos creados
        ], 201); 
    }

    public function destroy(Facility $facility)
    {
        // Elimina el aula
        $facility->delete();

        // Redirige de vuelta a la lista de aulas (la página principal)
        // También puedes añadir un mensaje de sesión para mostrar una notificación.
        return Redirect::route('Facilities.index')->with('success', 'Aula eliminada correctamente.');
    }
}
