<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;
//Control Administrativo
use App\Http\Controllers\Cursos\CompletionController;
use App\Http\Controllers\Ajustes\AjustesController;
use App\Http\Controllers\Control_admin\ControlAdministrativoController;
use App\Http\Controllers\AdmonCont\HorarioController;
use App\Http\Controllers\AdmonCont\FacilityController;
use App\Http\Controllers\AdmonCont\store\ListsControler;
use App\Http\Controllers\AdmonCont\store\studentController;
use App\Http\Controllers\AdmonCont\MateriaController;
use App\Http\Controllers\AdmonCont\store\teacherController;
use App\Http\Controllers\SchoolarCont\InscripcionController;

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
    Route::get('/bienvenido', function () {return view('Dashboard.index');
    })->name('dashboard');

    // Mi Informacion
    Route::get('/mi-informacion', function () { 
        return view('layouts.MiInformacion.index'); 
    })->name('MiInformacion.index');

    // Cursos
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
    
    // --- Gestión de cursos - solo para admins y docentes ---
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

    // --- Rutas de Progreso (Para todos los usuarios) ---
    Route::post('/completions/mark', [CompletionController::class, 'mark'])
        ->name('completions.mark'); // <- MOVIMOS AQUÍ
    
    Route::post('/actividades/{activity}/submit', [ActivitiesController::class, 'submit'])
        ->name('activities.submit'); // <- AÑADIMOS ESTA RUTA

    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])
        ->name('courses.certificate');

    //Vista del curso
    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');
    
    // Rutas para inscribir y desinscribir al usuario (para AJAX)
    Route::post('/cursos/{course}/inscribir', [CourseController::class, 'enroll'])
        ->name('courses.enroll');
    
    Route::post('/cursos/{course}/desinscribir', [CourseController::class, 'unenroll'])
        ->name('courses.unenroll');


    Route::middleware(['role:master,control_administrativo']) 
        ->prefix('ajustes')->name('ajustes.')->group(function () {
        
        // --- Tus rutas existentes ---
        Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
        Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
        
        // --- 2. AÑADIMOS LAS RUTAS FALTANTES PARA EL MODAL ---
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

        Route::get('/lista-estudiantes', [studentController::class, 'index'])->name('Listas.students.index');
    //Editar Alumno
    Route::get('/lista-estudiantes/{id}/edit',[InscripcionController::class, 'edit'])->name('Listas.students.edit');
    Route::put('/lista-estudiantes/{id}',[InscripcionController::class, 'update'])->name('Listas.students.update');
    
    Route::get('/lista-docentes', [teacherController::class, 'index'])->name('Listas.members.index');

    Route::get('/lista-docentes/registro', [teacherController::class, 'form'])->name('Listas.members.form');

    Route::post('/lista-docentes/create', [teacherController::class, 'store'])->name('Listas.members.create');
    //Editar Alumno
    Route::get('/lista-docentes/{id}/edit',[teacherController::class, 'edit'])->name('Listas.members.edit');
    Route::put('/lista-docentes/{id}',[teacherController::class, 'update'])->name('Listas.members.update');


    //Materias

    Route::get('/listas/materias', [MateriaController::class, 'index'])->name('Listas.materias.index');
    Route::post('/listas/materias/create', [MateriaController::class, 'store'])->name('Listas.materias.store');
    Route::put('/listas/materias/{registro}', [MateriaController::class, 'update'])->name('Listas.materias.update');


    //Aulas
    Route::get('/aulas',[FacilityController::class, 'index'])->name('Facilities.index');
        //Crear
    Route::get('/aulas/crear',[FacilityController::class, 'createForm'])->name('Facilities.create.form');
        //Guardar
    Route::post('/aulas', [FacilityController::class, 'store'])->name('Facilities.store');

    Route::delete('/aulas/{facility}', [FacilityController::class, 'destroy'])->name('Facilities.destroy');


    //Horarios
    Route::get('/horarios', [HorarioController::class, 'index'])->name('Horarios.index');
        //Guardar
    Route::post('/horarios', [HorarioController::class, 'store'])->name('Horarios.store');
        //Eliminar
    Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->name('horarios.destroy');
        //Editar
    Route::get('/horarios/{horario}/edit', [HorarioController::class, 'edit'])->name('horarios.edit');
    Route::put('/horarios/{horario}', [HorarioController::class, 'update'])->name('horarios.update');
    
    //Clases
    Route::get('/clases', [generalController::class, 'index'])->name('Clases.index');
    
    //Carreras
    Route::get('/carreras', [generalController::class, 'index'])->name('Carreras.index');//Mostrar Carreras
    Route::get('/carreras/create',[careerController::class,'create'])->name('career.create');//Mostrar Formulario de Creación
    Route::post('/carreras', [careerController::class, 'store'])->name('career.store');//Crear Carrera
    Route::put('/carreras/{carrera}', [careerController::class, 'update'])->name('career.update');
    Route::delete('/carreras/{carrera}', [careerController::class, 'destroy'])->name('career.destroy');//Eliminar Carrera
    
    });

    //Control Escolar

    //Inscripción
    Route::middleware(['role:master'])->group(function () {
        Route::get('/inscripcion',[InscripcionController::class, 'index'])->name('Inscripción.index');//Formulario de inscripción
        Route::post('/inscripcion/create',[InscripcionController::class, 'store'])->name('Inscripcion.store');//Registrar Alumno
    });
    

    //V1-DEPRECATED
    Route::middleware(['role:master'])->group(function () {
        Route::get('/control-administrativo', function () { return view('layouts.ControlAdmin.index'); 
        })->name('ControlAdmin.index');
    });

    Route::middleware(['role:master'])->group(function () {
        Route::get('/ajustes', function () { return view('layouts.Ajustes.index'); 
        })->name('Ajustes.index');
    });



});

   

