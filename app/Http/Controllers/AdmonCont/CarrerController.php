<?php

namespace App\Http\Controllers\AdmonCont;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrerRequest;


class CarrerController extends Controller
{
    use AuthorizesRequests;
    /**
     * Muestrar lista de cursos
     */
    public function index(): View
    {
        
        return view('layouts.ControlAdmin.Carreras.index',);
    }

    public function create(): View
    {
        $course = Course::all();
        $institutions = Institution::all();

        return view('layouts.Cursos.create',compact('course'), compact('institutions'));
    }

    
}
