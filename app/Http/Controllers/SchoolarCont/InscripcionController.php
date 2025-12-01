<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

// Modelos Necesarios
use App\Models\Users\User;
use App\Models\Users\Address;
use App\Models\Users\Career; 
use App\Models\Users\AcademicProfile;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Users\Enrollment; 
use App\Models\Users\Period;
use App\Models\Facturacion\Billing; 
class InscripcionController extends Controller
{
    // =========================================================================
    // 1. MOSTRAR FORMULARIO (INDEX / CREATE)
    // =========================================================================
    
    public function index(){
        $periodoActivo = Period::where('is_active', 1)->first();

        if (!$periodoActivo) {
            session()->flash('error', '⛔ NO SE PUEDE INSCRIBIR: No hay un Periodo Académico activo configurado.');
        }

        $carreras = Career::all();
        $departamentos = Department::all(); 
        $puestos = Workstation::all();      

        return view('layouts.ControlEsc.Inscripcion.index', compact('carreras', 'departamentos', 'puestos', 'periodoActivo'));
    }

    public function create()
    {
        return $this->index(); // Reutilizamos la lógica del index
    }

    // =========================================================================
    // 2. GUARDAR NUEVO ASPIRANTE (STORE)
    // =========================================================================
public function store(Request $request)
    {
        // 1. Validar Periodo
        $periodoActivo = Period::where('is_active', 1)->first();
        if (!$periodoActivo) {
            return back()->with('error', 'Error crítico: El periodo se cerró durante el proceso.');
        }

        // VALIDACIÓN DE DATOS ENTRANTES
        $request->validate([
        'nombre' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'email' => 'required|email',
        'telefono' => 'required|string',
        'fecha_nacimiento' => 'required|date',
        'carrera_id' => 'required|exists:careers,id', 
        'calle' => 'required',
        'colonia' => 'required',
    ]);
        DB::beginTransaction();

        try {
            // Generar RFC si no viene
            $rfcFinal = $request->RFC ?? ('XAXX010101000' . rand(100, 999));

            // Crear Dirección
            $address = Address::create([
                'calle' => $request->calle, 'colonia' => $request->colonia,
                'ciudad' => $request->ciudad, 'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ]);

            // Crear Usuario
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make('TMP_' . uniqid()), 
                'RFC' => $rfcFinal,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
                'address_id' => $address->id,
                'institution_id' => 4, 
                'department_id' => $request->has('is_anfitrion') ? $request->department_id : null,
                'workstation_id' => $request->has('is_anfitrion') ? $request->workstation_id : null,
                'role_id' => 7,
                
            ]);

            // Asignar Rol
            $user->roles()->attach(7, ['institution_id' => 4]); 

            // Subir Documentos
            $rutasDocs = $this->subirDocumentos($request, $user->id);

            // 5. Crear Perfil Académico (CORREGIDO)
            $academicProfile = AcademicProfile::create([
                'user_id' => $user->id,
                // CAMBIO AQUÍ: Usamos la variable correcta
                'career_id' => $request->carrera_id, 
                'semestre' => 1,
                'status' => 'Aspirante', 
                'is_anfitrion' => $request->has('is_anfitrion'),
                'doc_acta_nacimiento' => $rutasDocs['doc_acta_nacimiento'] ?? null,
                'doc_certificado_prepa' => $rutasDocs['doc_certificado_prepa'] ?? null,
                'doc_curp' => $rutasDocs['doc_curp'] ?? null,
                'doc_ine' => $rutasDocs['doc_ine'] ?? null,
            ]);

            // 6. Crear Historial (CORREGIDO)
            Enrollment::create([
                'user_id' => $user->id,
                // CAMBIO AQUÍ: Usamos la variable correcta
                'career_id' => $request->carrera_id,
                'semestre' => 1,
                'periodo' => $periodoActivo->name,
                'status' => 'Pendiente',
                'doc_acta_nacimiento' => $rutasDocs['doc_acta_nacimiento'] ?? null,
                'doc_certificado_prepa' => $rutasDocs['doc_certificado_prepa'] ?? null,
                'doc_curp' => $rutasDocs['doc_curp'] ?? null,
                'doc_ine' => $rutasDocs['doc_ine'] ?? null,
            ]);

            // 7. Generar Factura
            Billing::create([
                'user_id'           => $user->id,
                'period_id'         => $periodoActivo->id,
                'factura_uid'       => 'INS-' . strtoupper(uniqid()),
                'concepto'          => 'Inscripción Nuevo Ingreso', 
                'monto'             => 1500.00, 
                'fecha_vencimiento' => Carbon::now()->addDays(7),
                'status'            => 'Pendiente', 
                'description'       => 'Cargo automático por registro de aspirante.'
            ]);

            DB::commit();
            return redirect()->route('escolar.students.index')
                ->with('success', 'Aspirante registrado. Factura de Inscripción generada (Pendiente de Pago).');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 7. MOSTRAR EDICIÓN / REINSCRIPCIÓN (EDIT)
    // =========================================================================
    public function edit(string $id)
    {
        $periodoActivo = Period::where('is_active', 1)->first();
        if (!$periodoActivo) {
            session()->flash('error', 'No hay periodo activo para reinscripciones.');
        }

        $user = User::with(['address', 'academicProfile'])->findOrFail($id);
        $carreras = Career::all();
        $departamentos = Department::all();
        $puestos = Workstation::all();
        $historialInscripciones = Enrollment::where('user_id', $id)->orderBy('created_at', 'desc')->get();

        return view('layouts.ControlEsc.Inscripcion.index', [
            'alumno' => $user,
            'carreras' => $carreras,
            'periodoActivo' => $periodoActivo,
            'historialInscripciones' => $historialInscripciones,
            'departamentos' => $departamentos,
            'puestos' => $puestos
        ]);
    }

    // =========================================================================
    // 8. PROCESO DE REINSCRIPCIÓN (UPDATE)
    // =========================================================================
public function update(Request $request, string $id)
    {
        $periodoActivo = Period::where('is_active', 1)->first();
        if (!$periodoActivo) return back()->with('error', 'No hay periodo activo.');

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $perfil = $user->academicProfile;

            // 1. Actualizar Datos Personales
            $user->update([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'telefono' => $request->telefono,
                // Asegúrate de agregar todos los campos editables aquí
                'RFC' => $request->RFC,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
                // Dirección también debería actualizarse si cambió
                // 'address_id' => ... (o actualizar la relación address)
            ]);
            
            // Actualizar la Dirección
            if ($user->address) {
                $user->address->update([
                    'calle' => $request->calle,
                    'colonia' => $request->colonia,
                    'ciudad' => $request->ciudad,
                    'estado' => $request->estado,
                    'codigo_postal' => $request->codigo_postal,
                ]);
            }

            // 2. Lógica de Semestre y Carrera
            $nuevoSemestre = $perfil->semestre + 1;
            
            // Leemos la carrera del formulario 
            $nuevaCarreraId = $request->carrera_id ?? $perfil->career_id;

            // 3. Subir Nuevos Documentos
            $nuevosDocs = $this->subirDocumentos($request, $user->id);
            
            // MEJORA: Preparamos el array final de documentos para el historial
            // Si hay nuevo, usa nuevo. Si no, usa el del perfil actual.
            $docsFinales = [
                'doc_acta_nacimiento' => $nuevosDocs['doc_acta_nacimiento'] ?? $perfil->doc_acta_nacimiento,
                'doc_certificado_prepa' => $nuevosDocs['doc_certificado_prepa'] ?? $perfil->doc_certificado_prepa,
                'doc_curp' => $nuevosDocs['doc_curp'] ?? $perfil->doc_curp,
                'doc_ine' => $nuevosDocs['doc_ine'] ?? $perfil->doc_ine,
            ];

            // 4. Actualizar Perfil Académico
            // Usamos array_filter para solo actualizar en la BD los que traen archivo nuevo
            $perfil->update(array_merge(array_filter($nuevosDocs), [
                'semestre' => $nuevoSemestre,
                'career_id' => $nuevaCarreraId,
                'status' => 'Inactivo', 
                'is_anfitrion' => $request->has('is_anfitrion'),
            ]));

            // 5. Crear Historial (Enrollment) CON LOS DOCUMENTOS CORRECTOS
            Enrollment::create(array_merge($docsFinales, [ // <--- Usamos $docsFinales aquí
                'user_id' => $user->id,
                'career_id' => $nuevaCarreraId,
                'semestre' => $nuevoSemestre,
                'periodo' => $periodoActivo->name,
                'status' => 'Pendiente',
            ]));

           

            // Factura de Reinscripción (Lógica Inteligente)
            $facturaExiste = Billing::where('user_id', $user->id)
                            ->where('period_id', $periodoActivo->id)
                            ->where('concepto', 'like', '%Reinscripción%')
                            ->exists();

            if (!$facturaExiste) {
                Billing::create([
                    'user_id' => $user->id,
                    'period_id' => $periodoActivo->id,
                    'factura_uid' => 'RE-' . strtoupper(uniqid()),
                    'concepto' => 'Reinscripción Semestre ' . $nuevoSemestre, 
                    'monto' => 1200.00, 
                    'fecha_vencimiento' => Carbon::now()->addDays(5),
                    'status' => 'Pendiente',
                ]);
            }

            DB::commit();
            return redirect()->route('escolar.students.index')->with('success', 'Reinscripción procesada.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('escolar.students.index')->with('success', 'Usuario eliminado.');
    }

    // --- Helper Privado ---
    private function subirDocumentos($request, $userId) {
        $rutas = [];
        $campos = ['doc_acta_nacimiento', 'doc_certificado_prepa', 'doc_curp', 'doc_ine'];
        foreach ($campos as $campo) {
            if ($request->hasFile($campo)) {
                $rutas[$campo] = $request->file($campo)->store("documentos/{$userId}/expediente", 'public');
            }
        }
        return $rutas;
    }
}