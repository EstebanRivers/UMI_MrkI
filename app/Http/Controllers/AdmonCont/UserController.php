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
        // Obtener el nombre de la ruta.
        $routeName = $request->route()->getName();

        $listType = '';
        if ($routeName === 'Listas.students.index') {
            $listType = 'students';
        } elseif ($routeName === 'Listas.members.index') {
            $listType = 'members';
        } elseif ($routeName === 'Listas.users.index') {
            $listType = 'users';
        } elseif ($routeName === 'Listas.courses.index'){
            $listType = 'courses';
        } else {

        }

        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            // 'data' => $data // Aquí pasarías los datos si los tuvieras
        ]);
        
    }
}
