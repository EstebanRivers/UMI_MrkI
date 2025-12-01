<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Address;
use App\Models\Users\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class teacherController extends Controller
{
    //
    public function index(Request $request): View
    {
        $listType = 'members'; // Definimos el tipo de lista fijo
        $roleName = 'docente'; // Definimos el rol fijo

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
            'carrera_id'
        ];

        $careerColumns=[
            'official_id',
            'name',
            'id'
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
            ->with(['academicProfile.career' => function (Relation $query) use ($careerColumns) {
                // Selecciona las columnas de la carrera (incluyendo 'name')
                $query->select($careerColumns);
            }])
            ->get(); // Ejecuta la consulta y obtiene la colección de resultados

        // --- Devolución de la Vista ---
        
        // La ruta de la vista ahora es fija para estudiantes
        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }

    public function form(){
        // 1. Cargar las Carreras
        // Asume que el modelo se llama 'Carrera' y tiene las columnas 'id' y 'nombre'.
        $carreras = Career::all();

        return view('layouts.ControlAdmin.Listas.members.create', compact('carreras' /*, 'campuses' */));
    }

    public function store(Request $request){
        // --- 1. VERIFICACIÓN / VALIDACIÓN DE DATOS ---
        $request->validate([
            // Reglas del Modelo User
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['nullable', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Reglas de la Dirección
            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            
            // Reglas Académicas
            'carrera' => ['required', 'integer', Rule::exists('carrers', 'id')],
        ]);
            // 1.2. PREPARACIÓN DE DATOS (RFC Genérico) ✅
            // ----------------------------------------------------
            $rfcFinal = $request->RFC;

            if (empty($rfcFinal)) {
                $rfcBase = 'XAXX010101000';
                $rfcFinal = $rfcBase;
                
                // Si el RFC está vacío, verificamos si el genérico ya existe.
                // Si ya existe, buscamos el RFC genérico más alto y le sumamos 1.
                $existingRfcCount = User::where('RFC', 'like', $rfcBase . '%')->count();
                
                if ($existingRfcCount > 0) {
                    // Ejemplo: Si ya existe XAXX010101000 y XAXX010101001, asigna XAXX010101002
                    $suffix = str_pad($existingRfcCount, 3, '0', STR_PAD_LEFT);
                    $rfcFinal = substr($rfcBase, 0, -3) . $suffix; // Ajusta el número de caracteres si es necesario
                }
            }
            // ----------------------------------------------------

        DB::beginTransaction();

        try {
            // --- 1. GUARDAR LA DIRECCIÓN (MODELO ADDRESS) 📍 ---
            $address = Address::create([
                'calle' => $request->calle,
                'colonia' => $request->colonia,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ]);

            // --- 2. GUARDAR EL USUARIO (MODELO USER) 👤 ---
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'RFC' => $rfcFinal,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
                'address_id' => $address->id, // ¡Vinculado correctamente!
            ]);
            // --- 3. ASIGNAR EL ROL 'ALUMNO' (TABLA PIVOTE user_roles_institution) 🔑 ---
            
            // Claves que se insertarán en la tabla pivote: user_roles_institution
            $roleIdAlumno = 6;  // Docente
            $institutionId = 4; // UMI

            $user->roles()->attach($roleIdAlumno, [
                // Laravel inserta este valor en la columna 'institution_id'
                'institution_id' => $institutionId, 
            ]);
            // --- 4. GUARDAR EL PERFIL ACADÉMICO (MODELO ACADEMICPROFILE) 🎓 ---

            $academicProfile = AcademicProfile::create([
                'user_id' => $user->id, // La clave foránea del usuario recién creado
                'carrera' => $request->carrera_id,
                'departamento' => $user->departamento,
                'status' => 'Activo' 
            ]);
            // --- 5. FINALIZACIÓN Y REDIRECCIÓN 🎉 ---

            // Si todos los pasos son exitosos, confirmamos los cambios
            DB::commit();
            return redirect()->route('Listas.members.index')->with('success', '¡El nuevo alumno ha sido registrado exitosamente!');
        } catch (\Exception $e) {
            // Si ocurre cualquier error, deshacemos todos los cambios
            DB::rollBack();


            // Redirigir de vuelta al formulario con un mensaje de error
            return back()->withInput()->with('error', 'Ocurrió un error en el registro. Inténtalo de nuevo.'. $e->getMessage());
            
        }

        

    }
    public function edit(string $id)
    {
        // 1. Buscar al usuario y cargar las relaciones necesarias
        // Usamos with(['address', 'academicProfile']) para cargar la información de dirección
        // y la información académica en una sola consulta, evitando problemas N+1.
        // findOrFail($id) asegura un error 404 si el ID no existe.
        $user = User::with(['address', 'academicProfile'])->findOrFail($id);

        // Opcional: Si quieres asegurar que solo se editen usuarios con el rol 'Alumno' (ID 7)
        // Descomenta la siguiente línea si es necesario
        
        if (!$user->roles()->where('role_id', 6)->exists()) {
            abort(403, 'Acceso no autorizado. Este usuario no es un alumno.');
        }
        
        
        // 2. Cargar la lista de carreras para llenar el dropdown
        $carreras = Career::all(); 

        // 3. Devolver la vista de edición con los datos
        return view('layouts.ControlAdmin.Listas.members.edit', compact('user', 'carreras'));
    }
}
