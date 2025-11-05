<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class studentController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'students'; // Definimos el tipo de lista fijo
        $roleName = 'estudiante'; // Definimos el rol fijo

        // --- Definición de Columnas ---
        
        // 1. Columnas a seleccionar de la tabla 'users'
        $userColumns = [
            'id',
            'nombre', 
            'apellido_paterno', 
            'apellido_materno',
            'created_at',
        ];
        
        // 2. Columnas a seleccionar de la tabla 'datos_academicos' (¡incluye user_id!)
        $academicColumns = [
            'user_id', // ¡CRUCIAL para la relación!
            'status', 
            'carrera'
        ];

        $careerColumns=[
            'official_id',
            'name',
        ];
        
        // --- Ejecución de la Consulta ---
        
        $dataList = User::query()
            // Filtra usuarios que tienen el rol 'estudiante'
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName); 
            })
            // Selecciona las columnas necesarias de la tabla 'users'
            ->select($userColumns)
            // Carga la relación 'academicProfile' con columnas específicas
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->get(); // Ejecuta la consulta y obtiene la colección de resultados

        // --- Devolución de la Vista ---
        
        // La ruta de la vista ahora es fija para estudiantes
        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }
    
    public function createForm()
    {
        // 1. Cargar la lista de TODOS los usuarios (sin filtro de rol)
        $users = User::select('id', 'nombre', 'apellido_paterno', 'apellido_materno')->get();
        
        // 2. Opciones del formulario
        $carreras = Career::select('id','official_id','name')->get();
        $statuses = ['Aspirante', 'Activo', 'Inactivo', 'Egresado']; // Añadimos 'aspirante'
        
        // 🚨 CRÍTICO: Esta variable es para construir la URL en el frontend.
        // Debe ser la parte inicial de la URL de tu ruta Listas.students.user.data.
        $userDataRoute = '/lista-estudiantes/usuario/'; 

        return view('layouts.ControlAdmin.Listas.students.create', compact('users', 'carreras', 'statuses', 'userDataRoute'));
    }

    public function getUserData(User $user)
    {
        // 1. Seleccionamos explícitamente los campos principales.
        // Esto asegura que la serialización JSON contenga 'nombre', 'apellido_paterno', etc.,
        // y resuelve los problemas de 'undefined' en el frontend.
        $userColumns = [
            'id', 
            'nombre', 
            'apellido_paterno', 
            'apellido_materno',
            // Añade cualquier otro campo que el frontend necesite
        ];

        // 2. Recargamos el usuario con solo los campos necesarios.
        $student = User::select($userColumns)
            ->where('id', $user->id)
            ->first();

        // Verificamos si la carga fue exitosa
        if (!$student) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }
        
        // 3. Cargamos la relación academicProfile.
        // Esto es NECESARIO para que el frontend chequee si 'user.academic_profile' existe 
        // y muestre la advertencia de actualización.
        $student->load('academicProfile'); 
        
        // 4. Devolvemos el modelo actualizado como JSON.
        return response()->json(['user' => $student]);
    }
}
