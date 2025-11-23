<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
use App\Models\Users\User;
use App\Models\Periods\Period;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('es');
        $user = Auth::user();
        
        // 1. Obtener Periodos ordenados
        $periods = Period::orderBy('start_date', 'desc')->get();

        // ======================================================
        // LÓGICA FUNCIONAL: CALCULAR MESES DINÁMICAMENTE
        // ======================================================
        $periods->map(function ($period) {
            if ($period->start_date && $period->end_date) {
                $start = Carbon::parse($period->start_date)->startOfMonth();
                $end = Carbon::parse($period->end_date)->endOfMonth();
                
                $monthRange = CarbonPeriod::create($start, '1 month', $end);
                
                $months = [];
                foreach ($monthRange as $date) {
                    $months[] = [
                        'label' => ucfirst($date->translatedFormat('F Y')), 
                        'key'   => $date->format('Y-m'),
                        'date'  => $date->format('Y-m-d')
                    ];
                }
                $period->setAttribute('meses_calculados', $months);
            } else {
                $period->setAttribute('meses_calculados', []);
            }
            return $period;
        });

        $viewData = ['user' => $user, 'periods' => $periods];
        $isAdmin = $user->hasActiveRole('master') || $user->hasActiveRole('control_administrativo');

        if ($isAdmin) {
            $query = User::query();
            
            // Filtro de búsqueda de usuarios
            if ($request->filled('search')) {
                $searchTerm = strtolower($request->input('search'));
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nombre', 'like', "%$searchTerm%")
                      ->orWhere('apellido_paterno', 'like', "%$searchTerm%")
                      ->orWhere('email', 'like', "%$searchTerm%");
                });
            }

            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['estudiante']);
            });

            $usersWithBillings = $query->with(['billings.payments', 'roles'])->orderBy('nombre')->get();

            // Filtro de Status (Lógica manual sobre la colección)
            if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                $usersWithBillings = $usersWithBillings->filter(function($user) use ($statusFilter) {
                    return $user->billings->contains(function($billing) use ($statusFilter) {
                        $estatus = $billing->status; 
                        $totalPagado = $billing->payments->sum('monto');
                        if ($estatus == 'Pendiente') {
                            if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                            elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                        }
                        return $estatus === $statusFilter;
                    });
                });
            }

            $viewData['usersWithBillings'] = $usersWithBillings;

        } else {
            // =========================================================
            // LÓGICA PARA ALUMNOS
            // =========================================================
            $query = Billing::where('user_id', $user->id)->with('payments');

            // Filtro de Búsqueda
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('concepto', 'like', "%{$searchTerm}%")
                      ->orWhere('factura_uid', 'like', "%{$searchTerm}%");
                });
            }
            
            // Filtro de Periodo
            if ($request->filled('period_id')) {
                $query->where('period_id', $request->input('period_id'));
            }

            $billings = $query->orderBy('fecha_vencimiento', 'desc')->get();

            // Filtro de Status (Igual que en admin)
            if ($request->filled('status')) {
                $statusFilter = $request->input('status');
                $billings = $billings->filter(function($billing) use ($statusFilter) {
                    $estatus = $billing->status; 
                    $totalPagado = $billing->payments->sum('monto');
                    if ($estatus == 'Pendiente') {
                        if ($totalPagado >= $billing->monto) { $estatus = 'Pagada'; } 
                        elseif ($totalPagado > 0) { $estatus = 'Abonado'; }
                    }
                    return $estatus === $statusFilter;
                });
            }

            $viewData['billings'] = $billings;
        }

        return view('layouts.Facturacion.index', $viewData);
    }
public function store(Request $request)
    {
        $loggedInUser = Auth::user();
        
        if (!$loggedInUser->hasActiveRole('master') && !$loggedInUser->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

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

        // --- VALIDACIÓN DE FECHAS ---
        $periodo = Period::find($validated['period_id']);
        $fechaFactura = Carbon::parse($validated['fecha']);
        
        if ($periodo->start_date && $periodo->end_date) {
            $inicio = Carbon::parse($periodo->start_date)->startOfDay();
            $fin = Carbon::parse($periodo->end_date)->endOfDay();

            if (!$fechaFactura->between($inicio, $fin)) {
                // FIX: Si falla la fecha, volvemos atrás con error, no intentamos hacer scroll
                return redirect()->back()
                    ->withErrors(['fecha' => "La fecha ({$validated['fecha']}) no coincide con el periodo seleccionado."])
                    ->withInput();
            }
        } 

        $filePath = $request->file('archivo')->store('facturas', 'public');
        $xmlPath = null;
        
        if ($request->hasFile('archivo_xml')) {
            $xmlPath = $request->file('archivo_xml')->store('facturas_xml', 'public');
        }
        
        $lastId = Billing::max('id') ?? 0;
        $newUid = "FAC-" . str_pad($lastId + 1, 3, "0", STR_PAD_LEFT);

        Billing::create([
            'factura_uid' => $newUid,
            'user_id' => $validated['user_id'],
            'period_id' => $validated['period_id'],
            'concepto' => $validated['concepto'],
            'monto' => $validated['monto'],
            'fecha_vencimiento' => $fechaFactura->format('Y-m-d'),
            'archivo_path' => $filePath,
            'xml_path' => $xmlPath,
            'status' => $validated['status'],
        ]);

        // --- REDIRECCIÓN BLINDADA (Store) ---
        $url = route('Facturacion.index');
    
        // 2. Construimos el ancla MANUALMENTE (concatenando string)
        // Esto obliga al navegador a recibirlo como un #hash real
        $anchor = '#target-user-' . $validated['user_id'] . '-period-' . $validated['period_id'];

        return redirect()->to($url . $anchor)->with('success', 'Factura creada exitosamente.');
    }

    public function destroy(Billing $billing)
    {
        $user = Auth::user();
        if (!$user->hasActiveRole('master') && !$user->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        if ($billing->archivo_path && $billing->archivo_path !== 'facturas/placeholder.pdf') { 
            Storage::disk('public')->delete($billing->archivo_path);
        }
        if ($billing->xml_path) { 
            Storage::disk('public')->delete($billing->xml_path);
        }

        // Guardamos los IDs antes de borrar para poder redirigir
        $userId = $billing->user_id;
        $periodId = $billing->period_id;

        $billing->delete();
        
        // --- REDIRECCIÓN BLINDADA (Destroy) ---
        // ... guardado ...

    // 1. Generamos la URL base
    $url = route('Facturacion.index');
    
    // 2. Construimos el ancla MANUALMENTE (concatenando string)
    // Esto obliga al navegador a recibirlo como un #hash real
    $anchor = '#target-user-' . $validated['user_id'] . '-period-' . $validated['period_id'];

    return redirect()->to($url . $anchor)->with('success', 'Factura creada exitosamente.');
    }
}