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
use PhpParser\Node\Stmt\TryCatch;



class studentController extends Controller
{
    // 1. MOSTRAR LISTA GENERAL
    public function index(Request $request)
    {
        // Consulta base: Solo usuarios con rol 'estudiante'
        $query = User::whereHas('roles', function($q) {
            $q->where('name', 'estudiante');
        })->with(['academicProfile.career']);

        // Buscador
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  // Búsqueda por matrícula también
                  ->orWhereHas('academicProfile', function($subQ) use ($search) {
                      $subQ->where('matricula', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenar por fecha de creación (más recientes primero)
        $dataList = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('layouts.ControlAdmin.Listas.students.index', compact('dataList'));
    }

    // 2. MOSTRAR FORMULARIO DE EDICIÓN / CONTRASEÑA
    // (Este es el método que te faltaba y causaba el error)
    public function edit($id)
    {
        $user = User::with('academicProfile')->findOrFail($id);
        
        // Cargar catálogos
        $departamentos = \App\Models\Users\Department::all();
        $puestos = \App\Models\Users\Workstation::all();
        
        // ¡ESTO FALTABA! Traer las carreras
        $carreras = \App\Models\Users\Career::all(); 

        return view('layouts.ControlAdmin.Listas.students.edit', compact('user', 'departamentos', 'puestos', 'carreras'));
    }

    // 3. GUARDAR CAMBIOS (DATOS O CONTRASEÑA)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validaciones básicas
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            // La contraseña es opcional (nullable), solo si la escriben se valida
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            // A. Actualizar Datos Personales
            $user->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'RFC' => $request->RFC,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
            ]);

            // B. Actualizar Datos Laborales (Si es anfitrión)
            if ($request->has('is_anfitrion')) {
                $user->department_id = $request->department_id;
                $user->workstation_id = $request->workstation_id;
                $user->save();
                
                // Actualizar flag en perfil académico
                $user->academicProfile->update(['is_anfitrion' => true]);
            } else {
                // Si desmarcaron anfitrión, limpiamos
                $user->department_id = null;
                $user->workstation_id = null;
                $user->save();
                $user->academicProfile->update(['is_anfitrion' => false]);
            }

            // C. Asignar Contraseña y Activar (Si se envió password)
            if ($request->filled('password')) {
                
                // Regla de Negocio: Solo permitir contraseña si tiene matrícula
                if (empty($user->academicProfile->matricula)) {
                    return back()->with('error', '⛔ No puedes asignar contraseña porque el alumno no tiene matrícula (falta pago).');
                }

                $user->password = Hash::make($request->password);
                $user->save();

                // CAMBIO DE ESTATUS FINAL: "Alumno Activo"
                // Solo si no estaba activo ya
                if ($user->academicProfile->status !== 'Alumno Activo') {
                    $user->academicProfile->status = 'Alumno Activo';
                    $user->academicProfile->save();
                }
            }

            DB::commit();

    
            return redirect()->route('escolar.students.index')
                ->with('success', 'Alumno actualizado y activado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}