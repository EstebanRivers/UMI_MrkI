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

class generalController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): View{
        $routeName = $request->route()->getName();
        $listType = match ($routeName) {
            'Clases.index' => 'Clases',
            'Horarios.index' => 'Horarios',
            default => null,
        };
        if (is_null($listType)) {
            abort(404); // Detener si la ruta no está definida en la lista
        }

        $viewPath = 'layouts.ControlAdmin.' . $listType . '.index';
        
        return view($viewPath, [
            // ...
        ]);
    }
}
