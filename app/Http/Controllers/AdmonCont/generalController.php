<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
// use App\Models\Course; // Asumiendo que existe
use App\Models\AdmonCont\Horario;
use App\Models\AdmonCont\HorarioClase;
use App\Models\Users\Career;
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
        //Obtener rutas
        $routeName = $request->route()->getName();
        //Mapeo de rutas con relación a modelos
        $modelMap = [
            //'Clases.index'   => Course::class,
            'Horarios.index' => HorarioClase::class,
            'Carreras.index' => Career::class,
        ];

        $listType = match ($routeName) {
            'Clases.index' => 'Clases.index',
            'Horarios.index' => 'Horarios.index',
            'Carreras.index' => 'Carreras.index',

            default => null,
        };
        if (is_null($listType)) {
            abort(404); // Detener si la ruta no está definida en la lista
        }

        $modelClass = $modelMap[$routeName];
        $data = $modelClass::all();

        $dataKey = strtolower(class_basename($modelClass)) . 's';

        $viewPath = 'layouts.ControlAdmin.' . $listType;
        
        return view($viewPath, [
            $dataKey => $data,
        ]);
    }
}
