<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\Billing;
use App\Models\Users\User; // Asegúrate que la ruta a tu modelo User sea correcta
use App\Models\Periods\Period; // Importa el modelo Period
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Para registrar errores
use Carbon\Carbon; // <-- ¡IMPORTANTE!

class BillingController extends Controller
{
    /**
     * Muestra la vista de facturación según el rol del usuario.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $periods = Period::orderBy('start_date', 'desc')->get();
        $viewData = ['user' => $user, 'periods' => $periods];
        $isAdmin = $user->hasActiveRole('master') || $user->hasActiveRole('control_administrativo');

        if ($isAdmin) {
            
            $query = User::query();

            // Lógica para qué usuarios mostrar (SOLO ESTUDIANTES)
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['estudiante']);
            });

            // (Filtro de rol ya no es necesario)

            // Cargar usuarios con sus facturas y pagos
            $usersWithBillings = $query->with([
                                        'billings' => function($q_billing) {
                                            $q_billing->with('payments'); // Carga los abonos
                                        }, 
                                        'roles'
                                    ])
                                    ->orderBy('nombre')
                                    ->get();

            // Filtrar la COLECCIÓN de usuarios por nombre/email si hay búsqueda
            if ($request->filled('search')) {
                 $searchTerm = strtolower($request->input('search'));
                 $usersWithBillings = $usersWithBillings->filter(function ($u) use ($searchTerm) {
                    return str_contains(strtolower($u->nombre), $searchTerm) ||
                           str_contains(strtolower($u->apellido_paterno), $searchTerm) ||
                           str_contains(strtolower($u->email), $searchTerm);
                 });
            }
            
            // Filtro de Status (aplicado a la colección)
            if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                
                $usersWithBillings = $usersWithBillings->filter(function($user) use ($statusFilter) {
                    return $user->billings->contains(function($billing) use ($statusFilter) {
                        $totalPagado = $billing->payments->sum('monto');
                        $estatus = 'Pendiente';
                        if ($totalPagado >= $billing->monto) $estatus = 'Pagada';
                        elseif ($totalPagado > 0) $estatus = 'Abonado';
                        
                        return $estatus === $statusFilter;
                    });
                });
            }

            $viewData['usersWithBillings'] = $usersWithBillings;

        } else {
            // --- REGLA: Docentes y Estudiantes solo ven sus propias facturas ---
            $query = Billing::where('user_id', $user->id)->with('payments'); // Cargar pagos

            if ($request->filled('search')) {
                 $searchTerm = $request->input('search');
                 $query->where(function ($q) use ($searchTerm) {
                    $q->where('concepto', 'like', "%{$searchTerm}%")
                      ->orWhere('factura_uid', 'like', "%{$searchTerm}%");
                });
            }
            if ($request->filled('period_id')) {
                $query->where('period_id', $request->input('period_id'));
            }
            
            $billings = $query->orderBy('fecha_vencimiento', 'desc')->get();

             if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                $billings = $billings->filter(function($billing) use ($statusFilter) {
                    $totalPagado = $billing->payments->sum('monto');
                    $estatus = 'Pendiente';
                    if ($totalPagado >= $billing->monto) $estatus = 'Pagada';
                    elseif ($totalPagado > 0) $estatus = 'Abonado';
                    return $estatus === $statusFilter;
                });
            }

            $viewData['billings'] = $billings;
        }

        return view('layouts.Facturacion.index', $viewData);
    }

    /**
     * Almacena una nueva factura.
     */
    public function store(Request $request)
    {
        $loggedInUser = Auth::user();
        
        if (!$loggedInUser->hasActiveRole('master') && !$loggedInUser->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        // --- VALIDACIÓN CORREGIDA ---
        $validated = $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            'fecha' => 'required|date', 
            'archivo' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required|in:Pendiente,Pagada',
            'user_id' => 'required|exists:users,id',
            'period_id' => 'required|exists:periods,id',
            'archivo_xml' => 'nullable|file|mimes:xml|max:2048' 
        ]);

        $filePath = $request->file('archivo')->store('facturas', 'public');

        $xmlPath = null; // Inicia como null
        if ($request->hasFile('archivo_xml')) {
        // Guarda en la carpeta 'facturas_xml' dentro del disco 'public"
        $xmlPath = $request->file('archivo_xml')->store('facturas_xml', 'public');
    }
        $lastId = Billing::count();
        $newUid = "FAC-" . str_pad($lastId + 1, 3, "0", STR_PAD_LEFT);

        Billing::create([
            'factura_uid' => $newUid,
            'user_id' => $validated['user_id'],
            'period_id' => $validated['period_id'], // <-- Esta línea guarda el ID
            'concepto' => $validated['concepto'],
            'monto' => $validated['monto'],
            'fecha_vencimiento' => Carbon::parse($validated['fecha'])->format('Y-m-d'),
            'archivo_path' => $filePath,
            'xml_path' => $xmlPath,
            'status' => $validated['status'],
        ]);
        
        return redirect()->route('Facturacion.index', ['_fragment' => 'user-anchor-' . $validated['user_id']])
                         ->with('success', 'Factura creada exitosamente.');
    }

    /**
     * Elimina una factura específica.
     */
    public function destroy(Billing $billing)
    {
        $user = Auth::user();
        if (!$user->hasActiveRole('master') && !$user->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        if ($billing->archivo_path && $billing->archivo_path !== 'facturas/placeholder.pdf') { 
            Storage::disk('public')->delete($billing->archivo_path);
        }
        
        $billing->delete();
        
        // Redirige CON ancla
        return redirect()->route('Facturacion.index', ['_fragment' => 'user-anchor-' . $billing->user_id])
                         ->with('success', 'Factura eliminada exitosamente.');
    }
}

