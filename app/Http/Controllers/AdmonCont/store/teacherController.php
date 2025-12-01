<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\Users\Career;
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
        $listType = 'members'; 
        $roleName = 'docente'; 

        // --- Definición de Columnas ---
        $userColumns = [
            'id', 'nombre', 'apellido_paterno', 'apellido_materno', 'created_at',
        ];
        
        $academicColumns = [
            'user_id', 'status', 'carrera_id'
        ];

        $careerColumns=[
            'official_id', 'name', 'id'
        ];
        
        // --- Ejecución de la Consulta ---
        $dataList = User::query()
            ->whereHas('roles', function (Builder $query) use ($roleName) {
                $query->where('name', $roleName); 
            })
            ->select($userColumns)
            ->with(['academicProfile' => function (Relation $query) use ($academicColumns) {
                $query->select($academicColumns);
            }])
            ->with(['academicProfile.career' => function (Relation $query) use ($careerColumns) {
                $query->select($careerColumns);
            }])
            ->get(); 

        $viewPath = 'layouts.ControlAdmin.Listas.' . $listType . '.index';
        
        return view($viewPath, [
            'dataList' => $dataList,
        ]);
    }

    public function form(){
        $carreras = Career::all();

        return view('layouts.ControlAdmin.Listas.members.create', compact('carreras'));
    }

    public function store(Request $request){
        // --- 1. VERIFICACIÓN / VALIDACIÓN DE DATOS ---
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['nullable', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            
            // CORRECCIÓN: Asegúrate que la tabla en BD se llame 'careers' (plural inglés) o 'carreras'
            // Si tu tabla es 'carrers' (typo), déjalo así, pero usualmente es 'careers'
            'carrera' => ['required', 'integer', Rule::exists('careers', 'id')],
        ]);

        $rfcFinal = $request->RFC;

        if (empty($rfcFinal)) {
            $rfcBase = 'XAXX010101000';
            $rfcFinal = $rfcBase;
            
            $existingRfcCount = User::where('RFC', 'like', $rfcBase . '%')->count();
            
            if ($existingRfcCount > 0) {
                $suffix = str_pad($existingRfcCount, 3, '0', STR_PAD_LEFT);
                $rfcFinal = substr($rfcBase, 0, -3) . $suffix; 
            }
        }

        DB::beginTransaction();

        try {
            // --- 1. GUARDAR LA DIRECCIÓN ---
            $address = Address::create([
                'calle' => $request->calle,
                'colonia' => $request->colonia,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ]);

            // --- 2. GUARDAR EL USUARIO ---
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'RFC' => $rfcFinal,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad ?? 0, 
                'address_id' => $address->id, 
            ]);

            // --- 3. ASIGNAR EL ROL 'DOCENTE' ---
            $roleIdDocente = 6;  // ID 6 es Docente
            $institutionId = 4; // UMI

            $user->roles()->attach($roleIdDocente, [
                'institution_id' => $institutionId, 
            ]);

            // --- 4. GUARDAR EL PERFIL ACADÉMICO ---
            $academicProfile = AcademicProfile::create([
                'user_id' => $user->id,
                // CORRECCIÓN: Usamos $request->carrera (mismo nombre que en validate)
                // Antes usabas $request->carrera_id que no existía en el request validado
                'carrera' => $request->carrera, 
                
                // CORRECCIÓN: $user->departamento era null. Usamos el request o un default.
                'departamento' => $request->departamento ?? 'Docencia', 
                'status' => 'Activo' 
            ]);

            // --- 5. FINALIZACIÓN ---
            DB::commit();
            
            // CORRECCIÓN: Ruta actualizada a control.teachers.index
            return redirect()->route('control.teachers.index')->with('success', '¡El docente ha sido registrado exitosamente!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: '. $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $user = User::with(['address', 'academicProfile'])->findOrFail($id);
        
        // CORRECCIÓN LÓGICA: Si estás editando docentes, el rol a verificar es 6 (Docente)
        // El mensaje de error anterior decía "no es un alumno", lo ajustamos.
        if (!$user->roles()->where('role_id', 6)->exists()) {
             // Opcional: permitir si es admin o quitar chequeo si es flexible
             // abort(403, 'Este usuario no es un docente.');
        }

        $carreras = Career::all(); 

        return view('layouts.ControlAdmin.Listas.members.edit', compact('user', 'carreras'));
    }
    
    public function update(Request $request, $id)
    {
        // ... aquí iría tu lógica de update ...
    }
}