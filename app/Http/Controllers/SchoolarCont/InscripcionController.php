<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    //Formulario de Inscripción
    public function index(){
        // 1. Cargar las Carreras
        // Asume que el modelo se llama 'Carrera' y tiene las columnas 'id' y 'nombre'.
        $carreras = Career::all();

        // 2. Cargar otros datos para dropdowns (ejemplo de Campus)
        // $campuses = Campus::orderBy('nombre', 'asc')->get(); 

        // 3. Retornar la vista 'create' con los datos
        return view('layouts.ControlEsc.Inscripcion.index', compact('carreras' /*, 'campuses' */));
    }
    public function store(){
        return view();
    }
    
}
