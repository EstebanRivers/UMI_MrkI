<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\Cursos\CourseController;



// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirigir raíz al dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// --- RUTAS DEL CONTEXTO ACTIVO (Requieren estar autenticado) ---
Route::middleware(['auth'])->group(function () {
    // 1. Ruta para establecer el contexto inicial al iniciar sesión (GET/POST)
    // No requiere {roleId} aquí, el controlador toma el rol por defecto.
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])
        ->name('context.set');

    // 2. Ruta para que el usuario pueda cambiar de rol (desde un botón en el sidebar, etc.)
    // Requiere el ID del rol al que quiere cambiar.
    Route::match(['get', 'post'], '/switch-role/{roleId}', [ContextController::class, 'setContext'])
        ->name('context.switch');

    // Dashboard - accesible para todos los usuarios autenticados
    Route::get('/dashboard', function () {return view('dashboard.index');});

    // Mi Informacion
    Route::get('/mi-informacion', function () { 
        return view('minformacion'); 
    })->name('layouts.MiInformacion.index');

    Route::get('/cursos', [CourseController::class, 'index'])->name('layouts.Cursos.index');

    // Facturación - solo para roles específicos
    Route::get('/facturacion', function () { return view('layouts.Facturacion.index'); });
    
    
    // Control Administrativo - roles especificos
    Route::middleware(['role:master'])->group(function () {
        Route::get('/control-administrativo', function () { return view('layouts.ControlAdmin.index'); });
    });

    Route::get('/ajustes', function () { return view('layouts.Ajustes.index'); });

});
