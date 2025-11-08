<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InscripcionController extends Controller
{
    //Formulario de Inscripción
    public function index(){
        return view('layouts.ControlEsc.Inscripcion.index');
    }
    public function store(){
        return view('layouts.ControlEsc.Inscripcion.index');
    }
}
