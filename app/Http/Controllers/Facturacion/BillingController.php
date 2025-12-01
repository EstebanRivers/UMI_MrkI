<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\BillingConcept; // Importamos el modelo de conceptos
use App\Models\Users\User;
use App\Models\Users\Period; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

        // 2. Obtener CONCEPTOS PREDEFINIDOS (Para el select del modal)
        // Solo traemos los activos de la institución actual
        $institutionId = session('active_institution_id');
        
        // Obtenemos los conceptos y los guardamos en una variable
        $conceptosDisponibles = BillingConcept::where('institution_id', $institutionId)
                                          ->where('is_active', 1)
                                          ->orderBy('concept')
                                          ->get();

        // ======================================================
        // LÓGICA FUNCIONAL: CALCULAR MESES DINÁMICAMENTE
        // ======================================================
        $periods->map(function ($period) {
            $fechasConfiguradas = $period->payment_dates ?? [];

            if ($period->start_date && $period->end_date) {
                $start = Carbon::parse($period->start_date)->startOfMonth();
                $end = Carbon::parse($period->end_date)->endOfMonth();
                
                $monthRange = CarbonPeriod::create($start, '1 month', $end);
                
                $months = [];
                $index = 0; 

                foreach ($monthRange as $date) {
                    if (isset($fechasConfiguradas[$index])) {
                        $fechaVencimiento = $fechasConfiguradas[$index]; 
                    } else {
                        $fechaVencimiento = $date->format('Y-m-d'); 
                    }

                    $months[] = [
                        'label' => ucfirst($date->translatedFormat('F Y')), 
                        'key'   => $date->format('Y-m'),
                        'date'  => $fechaVencimiento 
                    ];

                    $index++;
                }
                $period->setAttribute('meses_calculados', $months);
            } else {
                $period->setAttribute('meses_calculados', []);
            }
            return $period;
        });

        // Pasamos 'conceptosDisponibles' a la vista para que coincida con el @foreach del Blade
        $viewData = ['user' => $user, 'periods' => $periods, 'conceptosDisponibles' => $conceptosDisponibles];
        $isAdmin = $user->hasActiveRole('master') || $user->hasActiveRole('control_administrativo');

        if ($isAdmin) {
            // --- LÓGICA ADMIN ---
            $query = User::query();
            
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
            // --- LÓGICA ALUMNO ---
            $query = Billing::where('user_id', $user->id)->with('payments');

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

        // ==========================================================
        // 🚦 SISTEMA DE ALERTAS
        // ==========================================================
        $alertasVencimiento = [];

        if (!$isAdmin) {
            $facturasPorVencer = Billing::with('payments') 
                ->where('user_id', $user->id)
                ->whereIn('status', ['Pendiente', 'Abonado'])
                ->whereDate('fecha_vencimiento', '>=', Carbon::today())
                ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(7))
                ->get();

            foreach ($facturasPorVencer as $factura) {
                $totalPagado = $factura->payments->sum('monto');
                if ($totalPagado >= $factura->monto) {
                    continue; 
                }

                $fechaVencimiento = Carbon::parse($factura->fecha_vencimiento)->startOfDay();
                $diasRestantes = Carbon::today()->diffInDays($fechaVencimiento, false);

                $mensaje = '';
                $titulo = '';
                $tipo = 'info';
                $agregarAlerta = false;

                if ($diasRestantes <= 7 && $diasRestantes >= 3) {
                    $titulo = "📅 Recordatorio Amigable";
                    $mensaje = "Hola {$user->nombre}, te recordamos que tu factura '{$factura->concepto}' vence en {$diasRestantes} " . ($diasRestantes === 1 ? "día." : "días.");
                    $tipo = 'info';
                    $agregarAlerta = true;
                }
                elseif ($diasRestantes <= 2 && $diasRestantes >= -2) {
                    $tipo = 'warning';
                    $agregarAlerta = true;
                    if ($diasRestantes > 1) {
                        $titulo = "⚠ Atención: Pago Próximo";
                        $mensaje = "Hola {$user->nombre}, faltan {$diasRestantes} días para que venza tu factura '{$factura->concepto}'.";
                    } elseif ($diasRestantes === 1) {
                        $titulo = "⚠ Atención: Pago Próximo";
                        $mensaje = "Hola {$user->nombre}, falta 1 día para que venza tu factura '{$factura->concepto}'.";
                    } elseif ($diasRestantes === 0) {
                        $titulo = "⚠ Atención: Vence Hoy";
                        $mensaje = "Hola {$user->nombre}, tu factura '{$factura->concepto}' vence HOY.";
                    } else {
                        $titulo = "⚠ Atención: Su Pago Venció";
                        $mensaje = "Hola {$user->nombre}, tu factura '{$factura->concepto}' ya venció.";
                    }
                }
                elseif ($diasRestantes <= -3 && $diasRestantes >= -7) {
                    $titulo = "⛔ AVISO URGENTE DE BAJA";
                    $abs = abs($diasRestantes);
                    $mensaje = "Tu factura '{$factura->concepto}' venció hace {$abs} días.";
                    $tipo = 'error';
                    $agregarAlerta = true;
                }

                if ($agregarAlerta) {
                    $alertasVencimiento[] = [
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'tipo' => $tipo
                    ];
                }
            }
        }

        $viewData['alertasVencimiento'] = $alertasVencimiento;

        // Ruta de la vista (Layouts...)
        return view('layouts.Facturacion.index', $viewData);
    }

    public function store(Request $request)
    {
        $loggedInUser = Auth::user();
        
        if (!$loggedInUser->hasActiveRole('master') && !$loggedInUser->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        // VALIDACIÓN
        $validated = $request->validate([
            'concepto' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0',
            // IMPORTANTE: El modal envía "fecha", no "fecha_vencimiento"
            'fecha' => 'required|date', 
            'archivo' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required|in:Pendiente,Pagada',
            'user_id' => 'required|exists:users,id',
            'period_id' => 'required|exists:periods,id',
            'archivo_xml' => 'nullable|file|mimes:xml|max:2048' 
        ]);

        $periodo = Period::find($validated['period_id']);
        
        $filePath = $request->file('archivo')->store('facturas', 'public');
        $xmlPath = null;
        
        if ($request->hasFile('archivo_xml')) {
            $xmlPath = $request->file('archivo_xml')->store('facturas_xml', 'public');
        }
        
        $lastId = Billing::withTrashed()->max('id') ?? 0;
        $newUid = "FAC-" . str_pad($lastId + 1, 3, "0", STR_PAD_LEFT);

        // CREACIÓN
        Billing::create([
            'factura_uid' => $newUid,
            'user_id' => $validated['user_id'],
            'period_id' => $validated['period_id'],
            'concepto' => $validated['concepto'],
            'monto' => $validated['monto'],
            // Mapeamos el input 'fecha' a la columna 'fecha_vencimiento'
            'fecha_vencimiento' => $validated['fecha'], 
            'archivo_path' => $filePath,
            'xml_path' => $xmlPath,
            'status' => $validated['status'],
        ]);

        // REDIRECCIÓN CON ANCLAJE (SCROLL)
        $url = route('Facturacion.index', ['period_id' => $validated['period_id']]);
        
        // El ID debe coincidir exactamente con el <details> en la vista
        $anchor = '#factura-target-user-' . $validated['user_id'] . '-period-' . $validated['period_id'];

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

        $userId = $billing->user_id;
        $periodId = $billing->period_id;

        $billing->delete();
        
        // REDIRECCIÓN CON ANCLAJE
        $url = route('Facturacion.index', ['period_id' => $periodId]);
        $anchor = '#factura-target-user-' . $userId . '-period-' . $periodId;

        return redirect()->to($url . $anchor)->with('success', 'Factura eliminada exitosamente.');
    }
}