<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users\User; // CORRECCIÓN: Ruta exacta solicitada
use App\Models\AcademicProfile; 

class MatriculaController extends Controller
{
    /**
     * Muestra la lista de alumnos para gestión de matrículas.
     */
    public function index(Request $request)
    {
        // 1. Iniciar Query
        $query = User::with(['academicProfile.career', 'billings.payments']);

        // 2. Búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Paginación
        $students = $query->paginate(15);

        // 4. Transformación de datos (Status de Pago y Documentos)
        $students->getCollection()->transform(function ($student) {
            
            // Cálculo de Pago
            $inscripcionPagada = false;
            if ($student->relationLoaded('billings') && $student->billings) {
                foreach ($student->billings as $billing) {
                    $pagado = $billing->payments->sum('monto') >= $billing->monto;
                    if ($pagado) {
                        $inscripcionPagada = true;
                        break;
                    }
                }
            }
            $student->billing_status = $inscripcionPagada ? 'Pagado' : 'Pendiente';
            
            // Flags de documentos (Usando operador seguro ?->)
            $student->doc_certificado = $student->academicProfile?->doc_certificado_prepa ? true : false;
            $student->doc_acta = $student->academicProfile?->doc_acta_nacimiento ? true : false;
            $student->doc_curp = $student->academicProfile?->doc_curp ? true : false;

            return $student;
        });

        // 5. Filtro Rápido
        if ($request->get('filter_status') == 'pagados') {
            $filteredCollection = $students->getCollection()->filter(function ($student) {
                return $student->billing_status === 'Pagado';
            });
            $students->setCollection($filteredCollection);
        }

        return view('layouts.ControlEsc.Matriculas.index', [
            'dataList' => $students
        ]);
    }

    /**
     * Actualiza la matrícula.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'matricula' => 'required|string|max:20|unique:academic_profiles,matricula,' . $id . ',user_id',
        ]);

        try {
            $user = User::findOrFail($id);
            $profile = $user->academicProfile;
            
            if (!$profile) {
                return redirect()->back()->with('error', 'El alumno no tiene perfil académico.');
            }

            $profile->matricula = $request->matricula;
            $profile->save();

            return redirect()->back()->with('success', 'Matrícula asignada: ' . $request->matricula);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}