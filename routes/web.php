<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;
use App\Http\Controllers\Ajustes\AjustesController;
// Control Administrativo & Facturación (Fusionado)
use App\Http\Controllers\Facturacion\BillingController;
use App\Http\Controllers\Facturacion\PaymentController; 
use App\Http\Controllers\Control_admin\ControlAdministrativoController;
use App\Http\Controllers\AdmonCont\HorarioController;
use App\Http\Controllers\AdmonCont\FacilityController;
use App\Http\Controllers\AdmonCont\store\ListsControler; 
use App\Http\Controllers\AdmonCont\store\studentController;
use App\Http\Controllers\AdmonCont\store\careerController;
use App\Http\Controllers\AdmonCont\generalController;
use App\Http\Controllers\AdmonCont\MateriaController;
use App\Http\Controllers\AdmonCont\store\teacherController;
use App\Http\Controllers\SchoolarCont\InscripcionController;
use App\Http\Controllers\SchoolarCont\MatriculaController; 

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
    
    // --- Módulo de Facturación (Integrado desde rama Facturacion) ---
    // NOTA: He eliminado la ruta que retornaba solo la vista 'view' para usar el Controlador
    Route::get('/facturacion', [BillingController::class, 'index'])->name('Facturacion.index');
    Route::post('/facturacion', [BillingController::class, 'store'])->name('Facturacion.store'); 
    Route::delete('/facturacion/{billing}', [BillingController::class, 'destroy'])->name('Facturacion.destroy');
    Route::post('facturacion/payments', [PaymentController::class, 'store'])->name('payments.store');

    // --- Cursos (General - Alumnos y todos) ---
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])->name('courses.certificate');
    
    // Inscripción y Actividades (Alumnos)
    Route::post('/cursos/{course}/inscribir', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/cursos/{course}/desinscribir', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    // Route::post('/completions/mark', [CompletionController::class, 'mark'])->name('completions.mark'); // Descomentar si tienes el controlador
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
        ->name('ajustes.')
        ->group(function () {
            // Acciones Específicas
            Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])->name('users.toggleStatus');
            Route::post('periods/{id}/toggle-status', [AjustesController::class, 'togglePeriodStatus'])->name('periods.toggleStatus');

            // Rutas CRUD Dinámicas
            Route::get('/{seccion}/create-form', [AjustesController::class, 'getCreateForm'])->name('getCreateForm');
            Route::get('/{seccion}/{id}/edit-form', [AjustesController::class, 'getEditForm'])->name('getEditForm');
            Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
            Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
            Route::put('/{seccion}/{id}', [AjustesController::class, 'update'])->name('update');
            Route::delete('/{seccion}/{id}', [AjustesController::class, 'destroy'])->name('destroy');
        });
 // ==========================================
    // 1. MÓDULO CONTROL ACADÉMICO (Administrativo)
    // ==========================================
    Route::middleware(['role:master,control_administrativo'])
        ->prefix('control-administrativo')
        ->name('control.') 
        ->group(function () {

            Route::get('/academico', [ControlAdministrativoController::class, 'showAcademico'])->name('academico');
            Route::get('/planeacion', [ControlAdministrativoController::class, 'showPlaneacion'])->name('planeacion');

            // Alumnos & Docentes (Versión Admin)
            Route::get('/lista-estudiantes', [studentController::class, 'index'])->name('students.index');
            Route::get('/lista-estudiantes/{id}/edit',[InscripcionController::class, 'edit'])->name('students.edit');
            Route::put('/lista-estudiantes/{id}',[InscripcionController::class, 'update'])->name('students.update');
            Route::delete('/lista-estudiantes/{id}', [InscripcionController::class, 'destroy'])->name('students.destroy'); // FALTABA ESTA
            
            Route::get('/lista-docentes', [teacherController::class, 'index'])->name('teachers.index');
            Route::get('/lista-docentes/registro', [teacherController::class, 'form'])->name('teachers.form');
            Route::post('/lista-docentes/create', [teacherController::class, 'store'])->name('teachers.store');
            Route::get('/lista-docentes/{id}/edit',[teacherController::class, 'edit'])->name('teachers.edit');
            Route::put('/lista-docentes/{id}',[teacherController::class, 'update'])->name('teachers.update');

            // Materias & Aulas
            Route::get('/listas/materias', [MateriaController::class, 'index'])->name('subjects.index');
            Route::post('/listas/materias/create', [MateriaController::class, 'store'])->name('subjects.store');
            Route::put('/listas/materias/{registro}', [MateriaController::class, 'update'])->name('subjects.update');

            Route::get('/aulas',[FacilityController::class, 'index'])->name('facilities.index');
            Route::get('/aulas/crear',[FacilityController::class, 'createForm'])->name('facilities.create');
            Route::post('/aulas', [FacilityController::class, 'store'])->name('facilities.store');
            Route::delete('/aulas/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');

            Route::resource('horarios', HorarioController::class)->names('schedules');

            // Carreras
            Route::get('/carreras', [careerController::class, 'index'])->name('careers.index'); 
            Route::get('/carreras/create',[careerController::class,'create'])->name('careers.create');
            Route::post('/carreras', [careerController::class, 'store'])->name('careers.store');
            Route::put('/carreras/{carrera}', [careerController::class, 'update'])->name('careers.update');
            Route::delete('/carreras/{carrera}', [careerController::class, 'destroy'])->name('careers.destroy');
        });

  
// ==========================================
// 2. MÓDULO CONTROL ESCOLAR (Usa rol administrativo pero filtro por checkbox)
// ==========================================
Route::middleware(['role:master,control_administrativo']) 
    ->prefix('control-escolar')
    ->name('escolar.')
    ->group(function () {
        
        Route::get('/inicio', [ControlAdministrativoController::class, 'showEscolar'])->name('dashboard');

        // =============================================================
        // 1. INSCRIPCIÓN Y REINSCRIPCIÓN (InscripcionController)
        // =============================================================
        
        // A. Panel Principal (Index)
        Route::get('/inscripcion', [InscripcionController::class, 'index'])
            ->name('inscripcion.index');

        // B. Nuevo Ingreso (Create & Store)
        // FALTABA ESTA: Para ver el formulario vacío
        Route::get('/inscripcion/nuevo', [InscripcionController::class, 'create'])
            ->name('inscripcion.create');
            
        // Para guardar el formulario
        Route::post('/inscripcion/nuevo', [InscripcionController::class, 'store'])
            ->name('inscripcion.store');

        // C. Reinscripción (Edit & Update)
        // FALTABA ESTA: Para ver el formulario con datos del alumno
        Route::get('/inscripcion/{id}/reinscribir', [InscripcionController::class, 'edit'])
            ->name('inscripcion.edit');

        // Para guardar los cambios de la reinscripción
        Route::put('/inscripcion/{id}', [InscripcionController::class, 'update'])
            ->name('inscripcion.update');   
            
        // Alumnos (Versión Escolar)
        Route::get('/lista-alumnos', [studentController::class, 'index'])->name('students.index');
        Route::get('/lista-alumnos/{id}/edit', [studentController::class, 'edit'])->name('students.edit'); // Corregido: Usar studentController o InscripcionController según tu lógica
        Route::put('/lista-alumnos/{id}', [studentController::class, 'update'])->name('students.update');
        Route::delete('/lista-alumnos/{id}', [studentController::class, 'destroy'])->name('students.destroy');

        // Matrículas (¡Nuevo módulo!)
        Route::get('/matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
        Route::put('/matriculas/{id}', [MatriculaController::class, 'update'])->name('matriculas.update');
        Route::post('/matriculas/{id}/asignar', [MatriculaController::class, 'store'])->name('matriculas.store');

        // Placeholders
       // Route::get('/becas', function() { return view('layouts.ControlEsc.becas'); })->name('becas');
       // Route::get('/titulacion', function() { return 'Titulación'; })->name('titulacion');
    });

// --- Rutas Deprecadas (V1) ---
Route::get('/control-administrativo-v1', function () { return view('layouts.ControlAdmin.index'); })->middleware('role:master');
Route::get('/ajustes-v1', function () { return view('layouts.Ajustes.index'); })->middleware('role:master');
});