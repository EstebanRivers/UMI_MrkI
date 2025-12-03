<?php

namespace App\Http\Controllers\AdmonCont\store;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Address;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                  ->orWhereHas('academicProfile', function($subQ) use ($search) {
                      $subQ->where('matricula', 'like', "%{$search}%");
                  });
            });
        }

        $dataList = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('layouts.ControlAdmin.Listas.students.index', compact('dataList'));
    }

    // 2. MOSTRAR FORMULARIO DE EDICIÓN
    public function edit($id)
    {
        $user = User::with(['academicProfile', 'address'])->findOrFail($id);
        
        $departamentos = \App\Models\Users\Department::all();
        $puestos = \App\Models\Users\Workstation::all();
        $carreras = \App\Models\Users\Career::all(); 

        return view('layouts.ControlAdmin.Listas.students.edit', compact('user', 'departamentos', 'puestos', 'carreras'));
    }

    // 3. GUARDAR CAMBIOS (DATOS, ARCHIVOS Y CONTRASEÑA)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
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

            // Actualizar Dirección
            if ($user->address) {
                $user->address->update([
                    'calle' => $request->calle,
                    'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);
            }

            // B. Actualizar Datos Laborales (Si es anfitrión)
            $esAnfitrion = $request->has('is_anfitrion');
            if ($esAnfitrion) {
                $user->department_id = $request->department_id;
                $user->workstation_id = $request->workstation_id;
            } else {
                $user->department_id = null;
                $user->workstation_id = null;
            }
            $user->save();

            // C. LÓGICA DE ARCHIVOS Y PERFIL ACADÉMICO (Aquí estaba el faltante)
            // 1. Subir documentos (Retorna array solo con los nuevos)
            $nuevosDocs = $this->subirDocumentos($request, $user->id);

            // 2. Preparar datos del perfil
            // Nota: En tu vista el select se llama 'carrera', mapeamos a 'career_id'
            $datosPerfil = [
                'is_anfitrion' => $esAnfitrion,
                'career_id' => $request->carrera ?? $user->academicProfile->career_id,
                'semestre' => $request->semestre ?? $user->academicProfile->semestre,
            ];

            // 3. Fusionar datos + documentos nuevos (array_filter evita nulos)
            // Si el perfil no existe, lo crea. Si existe, lo actualiza.
            $user->academicProfile()->updateOrCreate(
                ['user_id' => $user->id],
                array_merge($datosPerfil, array_filter($nuevosDocs))
            );

            // D. Asignar Contraseña y Activar
            if ($request->filled('password')) {
                if (empty($user->academicProfile->matricula)) {
                    return back()->with('error', '⛔ No puedes asignar contraseña porque el alumno no tiene matrícula (falta pago).');
                }

                $user->password = Hash::make($request->password);
                $user->save();

                if ($user->academicProfile->status !== 'Alumno Activo') {
                    $user->academicProfile->status = 'Alumno Activo';
                    $user->academicProfile->save();
                }
            }

            DB::commit();
            return redirect()->route('escolar.students.index')
                ->with('success', 'Alumno actualizado correctamente (Datos y Documentos).');

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

    // --- HELPER PARA SUBIDA DE ARCHIVOS ---
    private function subirDocumentos($request, $userId) {
        $rutas = [];
        $campos = ['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine'];
        
        foreach ($campos as $campo) {
            if ($request->hasFile($campo)) {
                // Guarda en storage/app/public/documentos/{id}/expediente
                $rutas[$campo] = $request->file($campo)->store("documentos/{$userId}/expediente", 'public');
            }
        }
        return $rutas;
    }
}