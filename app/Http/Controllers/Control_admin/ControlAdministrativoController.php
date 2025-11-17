<?php

namespace App\Http\Controllers\Control_admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlAdministrativoController extends Controller
{
    
    public function showAcademico()
    {
        $user = Auth::user();
        $isMaster = $user->hasActiveRole('master');
        $userModules = @$user->academicProfile->modules ?? [];

        
        if (!$isMaster && !in_array('control_academico', $userModules)) {
            abort(403, 'No tienes permiso para esta sección.');
        }

        
        return view('layouts.ControlAdmin.academico');
    }

    
    public function showEscolar()
    {
        $user = Auth::user();
        $isMaster = $user->hasActiveRole('master');
        $userModules = @$user->academicProfile->modules ?? [];

        
        if (!$isMaster && !in_array('control_escolar', $userModules)) {
            abort(403, 'No tienes permiso para esta sección.');
        }

       
        return view('layouts.ControlAdmin.escolar');
    }

    
    public function showPlaneacion()
    {
        $user = Auth::user();
        $isMaster = $user->hasActiveRole('master');
        $userModules = @$user->academicProfile->modules ?? [];

        
        if (!$isMaster && !in_array('planeacion_vinculacion', $userModules)) {
            abort(403, 'No tienes permiso para esta sección.');
        }

        
        return view('layouts.ControlAdmin.planeacion');
    }
}
