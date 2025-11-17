<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;
use App\Http\Controllers\Ajustes\AjustesController;
use App\Http\Controllers\Control_admin\ControlAdministrativoController;


// Rutas de autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirigir raíz al dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// --- RUTAS DEL CONTEXTO ACTIVO (Requieren estar autenticado) ---
Route::middleware(['auth', 'ajax', 'spa'])->group(function () {
    // 1. Ruta para establecer el contexto inicial al iniciar sesión (GET/POST)
    // No requiere {roleId} aquí, el controlador toma el rol por defecto.
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])
        ->name('context.set');

    // 2. Ruta para que el usuario pueda cambiar de rol (desde un botón en el sidebar, etc.)
    // Requiere el ID del rol al que quiere cambiar.
    Route::match(['get', 'post'], '/context/switch/{institutionId}/{roleId}', [ContextController::class, 'setContext'])
        ->name('context.switch');

    // Dashboard - accesible para todos los usuarios autenticados
    Route::get('/bienvenido', function () {return view('dashboard.index');
    })->name('dashboard');

    // Mi Informacion
    Route::get('/mi-informacion', function () { 
        return view('layouts.MiInformacion.index'); 
    })->name('MiInformacion.index');

    // Cursos
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
       // Gestión de cursos - solo para admins y docentes
    Route::middleware(['role:master, docente'])->group(function () {
        Route::get('/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('/cursos/{course}/temas/crear', [TopicsController::class, 'create'])->name('course.topic.create');
        Route::post('/temas', [TopicsController::class, 'store'])->name('topics.store');
        Route::get('/temas/{topic}/edit', [TopicsController::class, 'edit'])->name('topics.edit'); 
        Route::put('/temas/{topic}', [TopicsController::class, 'update'])->name('topics.update');
        Route::delete('/temas/{topic}', [TopicsController::class, 'destroy'])->name('topics.destroy');
        Route::resource('topics.subtopics', SubtopicsController::class);
        Route::delete('/subtopics/{subtopic}', [SubtopicsController::class, 'destroy'])->name('subtopics.destroy');
        Route::post('/actividades', [ActivitiesController::class, 'store'])->name('activities.store');
        Route::delete('/actividades/{activity}', [ActivitiesController::class, 'destroy'])->name('activities.destroy');
    });
// Ajustes
Route::middleware(['role:master,control_administrativo'])
    ->prefix('ajustes')->name('ajustes.')->group(function () {
    
    // --- Rutas existentes ---
    Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
    Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
    
    // --- Rutas del Modal ---
    Route::get('/{seccion}/create-form', [AjustesController::class, 'getCreateForm'])->name('getCreateForm');
    Route::get('/{seccion}/{id}/edit-form', [AjustesController::class, 'getEditForm'])->name('getEditForm');
    Route::put('/{seccion}/{id}', [AjustesController::class, 'update'])->name('update');
    Route::delete('/{seccion}/{id}', [AjustesController::class, 'destroy'])->name('destroy');
    
    Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])
         ->name('users.toggleStatus'); 

    Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])
         ->name('users.toggleStatus');
         
    Route::post('periods/{id}/toggle-status', [AjustesController::class, 'togglePeriodStatus'])
         ->name('periods.toggleStatus');

});

    //Vista del curso
    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');


    // Facturación 
    Route::get('/facturacion', function () { return view('layouts.Facturacion.index'); 
    })->name('Facturacion.index');
    
    
    // Control Administrativo - roles especificos
    Route::middleware(['role:master,control_administrativo'])
        ->prefix('control-administrativo')
        ->name('control.') // Esto crea nombres como 'control.academico'
        ->group(function () {

            // Ruta para "Control Académico"
            Route::get('/academico', [ControlAdministrativoController::class, 'showAcademico'])
                 ->name('academico');

            // Ruta para "Control Escolar"
            Route::get('/escolar', [ControlAdministrativoController::class, 'showEscolar'])
                 ->name('escolar');

            // Ruta para "Planeación y Vinculación"
            Route::get('/planeacion', [ControlAdministrativoController::class, 'showPlaneacion'])
                 ->name('planeacion');


    });

   

});
