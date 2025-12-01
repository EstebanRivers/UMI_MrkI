<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\User;
use Illuminate\Http\Request;

class ListsControler extends Controller
{
    public function studentForm(){
        // 1. Obtiene TODOS los IDs de usuarios para el primer select
        $users = User::select('id', 'nombre', 'apellido_paterno')->get();
        
        // 2. Obtiene las carreras desde la tabla
        $carreras = Career::select('id', 'name')->get(); 
        
        // 3. Obtiene las opciones del ENUM 'status' del modelo AcademicProfile
        // Esto requiere un método que exponga los valores del ENUM, si no existe:
        // $statuses = AcademicProfile::getPossibleEnumValues('status');
        // Usaremos los valores hardcodeados aquí (ajusta a los reales de tu ENUM)
        $statuses = ['activo', 'inactivo', 'egresado', 'baja'];
        return view('layouts.ControlAdmin.Listas.students.create', compact('users', 'carreras', 'statuses'));
    }
    public function getUserData(User $user)
    {
        // Obtiene los datos básicos y carga el perfil académico si existe
        $data = $user->load('academicProfile'); 

        // Devuelve los datos como JSON
        return response()->json($data);
    }

    public function studentDestroy(){
        
    }

}
