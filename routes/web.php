<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;
use App\Http\Controllers\Cursos\CompletionController;
use App\Http\Controllers\Ajustes\AjustesController;
// Control Administrativo
use App\Http\Controllers\Control_admin\ControlAdministrativoController;
use App\Http\Controllers\AdmonCont\HorarioController;
use App\Http\Controllers\AdmonCont\FacilityController;
use App\Http\Controllers\AdmonCont\store\ListsControler; // Ojo: Revisa si es 'Controller' o 'Controler'
use App\Http\Controllers\AdmonCont\store\studentController;
use App\Http\Controllers\AdmonCont\store\careerController;
use App\Http\Controllers\AdmonCont\generalController;
use App\Http\Controllers\AdmonCont\MateriaController;
use App\Http\Controllers\AdmonCont\store\teacherController;
use App\Http\Controllers\SchoolarCont\InscripcionController;

// 1. Rutas Públicas y Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Redirección Raíz
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 2. GRUPO PRINCIPAL (Auth, Ajax, SPA)
Route::middleware(['auth', 'ajax', 'spa'])->group(function () {
    
    // --- Contexto (Cambio de Rol/Institución) ---
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])->name('context.set');
    Route::match(['get', 'post'], '/context/switch/{institutionId}/{roleId}', [ContextController::class, 'setContext'])->name('context.switch');

    // --- Dashboard General ---
    Route::get('/bienvenido', function () { return view('Dashboard.index'); })->name('dashboard');
    Route::get('/mi-informacion', function () { return view('layouts.MiInformacion.index'); })->name('MiInformacion.index');
    Route::get('/facturacion', function () { return view('layouts.Facturacion.index'); })->name('Facturacion.index');

    // --- Cursos (General) ---
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
    // --- Gestión de Cursos (Solo Docentes y Masters) ---
    Route::middleware(['role:master, docente'])->group(function () {
        Route::get('/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        
        // Temas y Subtemas
        Route::get('/cursos/{course}/temas/crear', [TopicsController::class, 'create'])->name('course.topic.create');
        Route::post('/temas', [TopicsController::class, 'store'])->name('topics.store');
        Route::get('/temas/{topic}/edit', [TopicsController::class, 'edit'])->name('topics.edit'); 
        Route::put('/temas/{topic}', [TopicsController::class, 'update'])->name('topics.update');
        Route::delete('/temas/{topic}', [TopicsController::class, 'destroy'])->name('topics.destroy');
        Route::resource('topics.subtopics', SubtopicsController::class);
        Route::delete('/subtopics/{subtopic}', [SubtopicsController::class, 'destroy'])->name('subtopics.destroy');
        
        // Actividades (Gestión)
        Route::post('/actividades', [ActivitiesController::class, 'store'])->name('activities.store');
        Route::delete('/actividades/{activity}', [ActivitiesController::class, 'destroy'])->name('activities.destroy');
    });

    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])->name('courses.certificate');
    
    // Inscripción AJAX
    Route::post('/cursos/{course}/inscribir', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/cursos/{course}/desinscribir', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    
    // Progreso y Actividades
    Route::post('/completions/mark', [CompletionController::class, 'mark'])->name('completions.mark');
    Route::post('/actividades/{activity}/submit', [ActivitiesController::class, 'submit'])->name('activities.submit');

    // --- Gestión de Cursos (Solo Docentes y Masters) ---
    Route::middleware(['role:master, docente'])->group(function () {
        Route::get('/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/cursos/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        
        // Temas y Subtemas
        Route::get('/cursos/{course}/temas/crear', [TopicsController::class, 'create'])->name('course.topic.create');
        Route::post('/temas', [TopicsController::class, 'store'])->name('topics.store');
        Route::get('/temas/{topic}/edit', [TopicsController::class, 'edit'])->name('topics.edit'); 
        Route::put('/temas/{topic}', [TopicsController::class, 'update'])->name('topics.update');
        Route::delete('/temas/{topic}', [TopicsController::class, 'destroy'])->name('topics.destroy');
        Route::resource('topics.subtopics', SubtopicsController::class);
        Route::delete('/subtopics/{subtopic}', [SubtopicsController::class, 'destroy'])->name('subtopics.destroy');
        
        // Actividades (Gestión)
        Route::post('/actividades', [ActivitiesController::class, 'store'])->name('activities.store');
        Route::delete('/actividades/{activity}', [ActivitiesController::class, 'destroy'])->name('activities.destroy');
    });

    // --- Módulo de Ajustes (Master y Admin) ---
    Route::middleware(['role:master,control_administrativo']) 
        ->prefix('ajustes')
        ->name('ajustes.') // Prefijo de nombre: 'ajustes.'
        ->group(function () {
            // Acciones Específicas (Deben ir ANTES de las rutas con comodines {seccion})
            Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])->name('users.toggleStatus');
            Route::post('periods/{id}/toggle-status', [AjustesController::class, 'togglePeriodStatus'])->name('periods.toggleStatus');

            // Rutas CRUD Dinámicas por Sección
            Route::get('/{seccion}/create-form', [AjustesController::class, 'getCreateForm'])->name('getCreateForm');
            Route::get('/{seccion}/{id}/edit-form', [AjustesController::class, 'getEditForm'])->name('getEditForm');
            Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
            Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
            Route::put('/{seccion}/{id}', [AjustesController::class, 'update'])->name('update');
            Route::delete('/{seccion}/{id}', [AjustesController::class, 'destroy'])->name('destroy');
        });

    // --- Módulo Control Administrativo ---
    Route::middleware(['role:master,control_administrativo'])
        ->prefix('control-administrativo')
        ->name('control.') // Prefijo de nombre: 'control.'
        ->group(function () {

            // Dashboards
            Route::get('/academico', [ControlAdministrativoController::class, 'showAcademico'])->name('academico');
            Route::get('/escolar', [ControlAdministrativoController::class, 'showEscolar'])->name('escolar');
            Route::get('/planeacion', [ControlAdministrativoController::class, 'showPlaneacion'])->name('planeacion');

            // Listas
            Route::get('/lista-estudiantes', [studentController::class, 'index'])->name('students.index');
            Route::get('/lista-estudiantes/{id}/edit',[InscripcionController::class, 'edit'])->name('students.edit');
            Route::put('/lista-estudiantes/{id}',[InscripcionController::class, 'update'])->name('students.update');
            
            Route::get('/lista-docentes', [teacherController::class, 'index'])->name('teachers.index');
            Route::get('/lista-docentes/registro', [teacherController::class, 'form'])->name('teachers.form');
            Route::post('/lista-docentes/create', [teacherController::class, 'store'])->name('teachers.store');
            Route::get('/lista-docentes/{id}/edit',[teacherController::class, 'edit'])->name('teachers.edit');
            Route::put('/lista-docentes/{id}',[teacherController::class, 'update'])->name('teachers.update');

            // Materias
            Route::get('/listas/materias', [MateriaController::class, 'index'])->name('subjects.index');
            Route::post('/listas/materias/create', [MateriaController::class, 'store'])->name('subjects.store');
            Route::put('/listas/materias/{registro}', [MateriaController::class, 'update'])->name('subjects.update');

            // Aulas (Facilities)
            Route::get('/aulas',[FacilityController::class, 'index'])->name('facilities.index');
            Route::get('/aulas/crear',[FacilityController::class, 'createForm'])->name('facilities.create');
            Route::post('/aulas', [FacilityController::class, 'store'])->name('facilities.store');
            Route::delete('/aulas/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');

            // Horarios (Resource)
            // Genera: control.schedules.index, control.schedules.store, etc.
            Route::resource('horarios', HorarioController::class)->names('schedules');

            // Carreras
            Route::get('/carreras', [careerController::class, 'index'])->name('careers.index'); 
            Route::get('/carreras/create',[careerController::class,'create'])->name('careers.create');
            Route::post('/carreras', [careerController::class, 'store'])->name('careers.store');
            Route::put('/carreras/{carrera}', [careerController::class, 'update'])->name('careers.update');
            Route::delete('/carreras/{carrera}', [careerController::class, 'destroy'])->name('careers.destroy');
        });

    // --- Inscripción (Control Escolar) ---
    // Ubicado aquí para aprovechar el auth group, pero con middleware específico
    Route::middleware(['role:master,control_escolar'])->group(function () {
        Route::get('/inscripcion',[InscripcionController::class, 'index'])->name('inscripcion.index');
        Route::post('/inscripcion/create',[InscripcionController::class, 'store'])->name('inscripcion.store');
    });

    // --- Rutas Deprecadas (V1) ---
    // Mantener solo si hay enlaces viejos que no has migrado
    Route::get('/control-administrativo-v1', function () { return view('layouts.ControlAdmin.index'); })->middleware('role:master');
    Route::get('/ajustes-v1', function () { return view('layouts.Ajustes.index'); })->middleware('role:master');

});