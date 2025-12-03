<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Users\User; 
use App\Models\Users\AcademicProfile; 

class MatriculaController extends Controller
{
    /**
     * Muestra la lista de alumnos para gestión de matrículas.
     */
    public function index(Request $request)
    {
        // 1. Iniciar Query
        $query = User::with(['academicProfile.career', 'billings.payments'])
                     ->whereHas('roles', function($q) {
                         $q->where('roles.id', 7); // Filtrar solo estudiantes
                     });

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

        // 4. Transformación de datos para la vista
        $students->getCollection()->transform(function ($student) {
            
            // --- CÁLCULO DE STATUS DE PAGO (INSCRIPCIÓN) ---
            // Buscamos por el prefijo del UID (INS- o RE-) en lugar del concepto
            $facturaInscripcion = $student->billings->filter(function($b) {
                return (str_starts_with($b->factura_uid, 'INS-') || str_starts_with($b->factura_uid, 'RE-'))
                        && $b->status != 'Cancelada';
            })->last(); 

            $statusPago = 'N/A'; 

            if ($facturaInscripcion) {
                $pagado = $facturaInscripcion->status === 'Pagada' || 
                          ($facturaInscripcion->payments->sum('monto') >= $facturaInscripcion->monto);
                
                $statusPago = $pagado ? 'Pagado' : 'Pendiente';
            } else {
                // Si no se le generó factura, está libre
                $statusPago = 'Pagado'; 
            }

            $student->billing_status = $statusPago;
            
            // Flags de documentos para los íconos
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
            $user = User::with('billings.payments')->findOrFail($id);
            
            // =========================================================
            // 🔒 LÓGICA DE CANDADO: VERIFICAR PAGO POR UID
            // =========================================================
            $facturaPendiente = $user->billings()
                ->where(function($q) {
                    $q->where('factura_uid', 'like', 'INS-%')
                      ->orWhere('factura_uid', 'like', 'RE-%');
                })
                ->where('status', '!=', 'Cancelada')
                ->latest()
                ->first();

            if ($facturaPendiente) {
                $totalPagado = $facturaPendiente->payments->sum('monto');
                $estaPagada = $facturaPendiente->status === 'Pagada' || ($totalPagado >= $facturaPendiente->monto);

                if (!$estaPagada) {
                    $deuda = $facturaPendiente->monto - $totalPagado;
                    return redirect()->back()->with('error', '⛔ ACCIÓN BLOQUEADA: El alumno tiene pendiente el pago: "' . $facturaPendiente->concepto . '" (Resta: $' . number_format($deuda, 2) . '). Debe liquidar para recibir matrícula.');
                }
            }
            // =========================================================

            $profile = $user->academicProfile;
            
            if (!$profile) {
                return redirect()->back()->with('error', 'El alumno no tiene perfil académico creado.');
            }

            $profile->matricula = $request->matricula;
            $profile->save();

            return redirect()->back()->with('success', '✅ Matrícula asignada correctamente: ' . $request->matricula);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error del sistema: ' . $e->getMessage());
        }
    }


public function uploadDocumento(Request $request, $id)
{
    // 1. Validar que venga el archivo
    $request->validate([
        'documento_pdf' => 'required|mimes:pdf|max:5120', // Máximo 5MB
    ]);

    $student = User::findOrFail($id);

    // 2. Subir el archivo
    if ($request->hasFile('documento_pdf')) {
        
        // Definir nombre único
        $filename = 'doc_' . $id . '_' . time() . '.pdf';
        
        // GUARDAR EN DISCO 'public' (Importante para poder verlo desde web)
        $path = $request->file('documento_pdf')->storeAs('documentacion_sep', $filename, 'public');

        // 3. Actualizar la Base de Datos
        // Asumiendo que la relación es 'academicProfile' y la tabla tiene la columna 'documento_path'
        $student->academicProfile()->updateOrCreate(
            ['user_id' => $student->id], // Condición de búsqueda
            ['documentoSEP_path' => $path]  // Datos a actualizar
        );
        
        return back()->with('success', 'Documento subido y vinculado correctamente.');
    }

    return back()->with('error', 'No se pudo subir el archivo.');
}
}