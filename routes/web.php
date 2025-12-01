<?php

use Illuminate\Support\Facades\Route;

// --- Controladores de Autenticación y Globales ---
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Users\ContextController;
use App\Http\Controllers\MiInformacion\MiInformacionController;
use App\Http\Controllers\Ajustes\AjustesController;

// --- Controladores LMS (Cursos) ---
use App\Http\Controllers\Cursos\CourseController;
use App\Http\Controllers\Cursos\TopicsController;
use App\Http\Controllers\Cursos\SubtopicsController;
use App\Http\Controllers\Cursos\ActivitiesController;

// --- Controladores de Facturación ---
use App\Http\Controllers\Facturacion\BillingController;
use App\Http\Controllers\Facturacion\PaymentController; 

// --- Controladores Administrativos y Escolares ---
use App\Http\Controllers\Control_admin\ControlAdministrativoController;
use App\Http\Controllers\AdmonCont\HorarioController;
use App\Http\Controllers\AdmonCont\FacilityController;
use App\Http\Controllers\AdmonCont\store\studentController;
use App\Http\Controllers\AdmonCont\store\careerController;
use App\Http\Controllers\AdmonCont\MateriaController;
use App\Http\Controllers\AdmonCont\store\teacherController;
use App\Http\Controllers\SchoolarCont\InscripcionController;
use App\Http\Controllers\SchoolarCont\MatriculaController; 
use App\Http\Controllers\Facturacion\BillingConceptController;

// ==========================================================================
// 1. ACCESO PÚBLICO
// ==========================================================================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==========================================================================
// 2. PLATAFORMA GENERAL (Usuarios Autenticados)
// ==========================================================================
Route::middleware(['auth', 'ajax', 'spa'])->group(function () {
    
    // --- Contexto (Cambio de Rol/Institución) ---
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])->name('context.set');
    Route::match(['get', 'post'], '/context/switch/{institutionId}/{roleId}', [ContextController::class, 'setContext'])->name('context.switch');

    // --- Dashboard ---
    Route::get('/bienvenido', function () { return view('Dashboard.index'); })->name('dashboard');

    // --- Módulo: Mi Información (Perfil) ---
    Route::prefix('mi-informacion')->name('MiInformacion.')->group(function () {
        Route::get('/', [MiInformacionController::class, 'index'])->name('index');

        // Submódulos para Alumnos y Docentes
        Route::middleware(['role:estudiante,docente,master'])->group(function () {
            Route::get('/clases', [MiInformacionController::class, 'showClases'])->name('clases');
            Route::get('/horario', [MiInformacionController::class, 'showHorario'])->name('horario');
            Route::get('/historial', [MiInformacionController::class, 'showHistorial'])->name('historial');
        });
    });

    // --- Módulo: Facturación (Vista General - Alumnos ven sus pagos, Admin ve todo) ---
    Route::get('/facturacion', [BillingController::class, 'index'])->name('Facturacion.index');

    // ======================================================================
    // 3. GESTIÓN ACADÉMICA (Docentes y Master)
    // ¡IMPORTANTE! Definir esto ANTES de las rutas genéricas de cursos
    // para evitar que /cursos/{course} capture /cursos/crear
    // ======================================================================
    Route::middleware(['role:master,docente'])->group(function () {
        // Gestión de Cursos
        Route::get('/cursos/crear', [CourseController::class, 'create'])->name('courses.create'); // <--- Ahora esta va primero
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
<<<<<<< HEAD

    // --- Módulo: Cursos (Vista y Realización - Alumnos y General) ---
    // Estas rutas atrapan {course}, por eso van AL FINAL de la sección de cursos
    Route::get('/cursos', [CourseController::class, 'index'])->name('Cursos.index');
    Route::get('/cursos/{course}', [CourseController::class, 'show'])->name('course.show');
    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])->name('courses.certificate');
    Route::get('/mis-certificados', [CourseController::class, 'myCertificates'])->name('courses.certificates.index');
=======
// Ajustes
Route::middleware(['role:master,control_administrativo'])
    ->prefix('ajustes')->name('ajustes.')->group(function () {
>>>>>>> parent of 0358ee6 (Fix: Reemplazo forzoso de Proyecto)
    
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
    Route::get('/cursos/{course}/certificado', [CourseController::class, 'showCertificate'])->name('courses.certificate');
    Route::get('/mis-certificados', [CourseController::class, 'myCertificates'])->name('courses.certificates.index');
    
    // Acciones del Alumno
    Route::post('/cursos/{course}/inscribir', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/cursos/{course}/desinscribir', [CourseController::class, 'unenroll'])->name('courses.unenroll');
    Route::post('/actividades/{activity}/submit', [ActivitiesController::class, 'submit'])->name('activities.submit');


    // ======================================================================
    // 4. ZONA ADMINISTRATIVA (Master y Control Administrativo)
    // ======================================================================
    Route::middleware(['role:master,control_administrativo'])->group(function () {

    // --- Gestión de Facturación (Cobros y Pagos) ---
    Route::post('/facturacion', [BillingController::class, 'store'])->name('Facturacion.store'); 
    Route::delete('/facturacion/{billing}', [BillingController::class, 'destroy'])->name('Facturacion.destroy');
     Route::post('facturacion/payments', [PaymentController::class, 'store'])->name('payments.store');
    
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

   

    

<<<<<<< HEAD

        // ------------------------------------------------------------
        // B. CONTROL ADMINISTRATIVO / ACADÉMICO (Infraestructura)
        // ------------------------------------------------------------
        Route::prefix('control-administrativo')->name('control.')->group(function () {
            
            Route::get('/academico', [ControlAdministrativoController::class, 'showAcademico'])->name('academico');
            Route::get('/planeacion', [ControlAdministrativoController::class, 'showPlaneacion'])->name('planeacion');

            // Docentes
            Route::get('/lista-docentes', [teacherController::class, 'index'])->name('teachers.index');
            Route::get('/lista-docentes/registro', [teacherController::class, 'form'])->name('teachers.form');
            Route::post('/lista-docentes/create', [teacherController::class, 'store'])->name('teachers.store');
            Route::get('/lista-docentes/{id}/edit',[teacherController::class, 'edit'])->name('teachers.edit');
            Route::put('/lista-docentes/{id}',[teacherController::class, 'update'])->name('teachers.update');

            // Carreras y Materias
            Route::get('/carreras', [careerController::class, 'index'])->name('careers.index'); 
            Route::get('/carreras/create',[careerController::class,'create'])->name('careers.create');
            Route::post('/carreras', [careerController::class, 'store'])->name('careers.store');
            Route::put('/carreras/{carrera}', [careerController::class, 'update'])->name('careers.update');
            Route::delete('/carreras/{carrera}', [careerController::class, 'destroy'])->name('careers.destroy');

            Route::get('/listas/materias', [MateriaController::class, 'index'])->name('subjects.index');
            Route::post('/listas/materias/create', [MateriaController::class, 'store'])->name('subjects.store');
            Route::put('/listas/materias/{registro}', [MateriaController::class, 'update'])->name('subjects.update');

            // Aulas y Horarios
            Route::get('/aulas',[FacilityController::class, 'index'])->name('facilities.index');
            Route::get('/aulas/crear',[FacilityController::class, 'createForm'])->name('facilities.create');
            Route::post('/aulas', [FacilityController::class, 'store'])->name('facilities.store');
            Route::delete('/aulas/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy');
            
            Route::resource('horarios', HorarioController::class)->names('schedules');
            
            // Espejo de Alumnos para Admin
            Route::get('/lista-estudiantes', [studentController::class, 'index'])->name('students.index');
            Route::get('/lista-estudiantes/{id}/edit', [studentController::class, 'edit'])->name('students.edit');
            Route::put('/lista-estudiantes/{id}', [studentController::class, 'update'])->name('students.update');
            Route::delete('/lista-estudiantes/{id}', [studentController::class, 'destroy'])->name('students.destroy');
        });


        // ------------------------------------------------------------
        // C. AJUSTES DEL SISTEMA
        // ------------------------------------------------------------
        Route::prefix('ajustes')->name('ajustes.')->group(function () {
            // Acciones Específicas (Toggles)
            Route::post('users/{id}/toggle-status', [AjustesController::class, 'toggleUserStatus'])->name('users.toggleStatus');
            Route::post('periods/{id}/toggle-status', [AjustesController::class, 'togglePeriodStatus'])->name('periods.toggleStatus');
            
            // CRUD Dinámico (Usuarios, Periodos, Departamentos, Puestos)
            Route::get('/{seccion}/create-form', [AjustesController::class, 'getCreateForm'])->name('getCreateForm');
            Route::get('/{seccion}/{id}/edit-form', [AjustesController::class, 'getEditForm'])->name('getEditForm');
            Route::post('/{seccion}', [AjustesController::class, 'store'])->name('store');
            Route::put('/{seccion}/{id}', [AjustesController::class, 'update'])->name('update');
            Route::delete('/{seccion}/{id}', [AjustesController::class, 'destroy'])->name('destroy');
            
            // Vista General (Al final por el comodín {seccion})
            Route::get('/{seccion}', [AjustesController::class, 'show'])->name('show');
        });

    }); // Fin Middleware Administrativo

}); // Fin Middleware Auth + Ajax + SPA
=======
});
>>>>>>> parent of 0358ee6 (Fix: Reemplazo forzoso de Proyecto)
