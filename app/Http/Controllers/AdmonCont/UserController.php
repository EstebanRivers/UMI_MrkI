<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): View
    {
        $routeName = $request->route()->getName();
        $listType = match ($routeName) {
        'Listas.students.index' => 'students',
        'Listas.members.index' => 'members',
        'Listas.users.index' => 'users',
        'Listas.materias.index' => 'materias',
        default => null, // Si la ruta no coincide con nada
    };

    if (is_null($listType)) {
        abort(404); // Detener si la ruta no está definida en la lista
    }

    $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
    
    return view($viewPath, [
        // ...
    ]);
        
    }
}
