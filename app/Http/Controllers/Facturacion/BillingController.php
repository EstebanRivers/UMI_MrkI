<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
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

        // ======================================================
        // LÓGICA FUNCIONAL: CALCULAR MESES DINÁMICAMENTE
        // ======================================================
        $periods->map(function ($period) {
            // 1. Obtenemos las fechas configuradas
            $fechasConfiguradas = $period->payment_dates ?? [];

            if ($period->start_date && $period->end_date) {
                $start = Carbon::parse($period->start_date)->startOfMonth();
                $end = Carbon::parse($period->end_date)->endOfMonth();
                
                $monthRange = CarbonPeriod::create($start, '1 month', $end);
                
                $months = [];
                $index = 0; 

                foreach ($monthRange as $date) {
                    
                    // 2. LÓGICA MAESTRA DE FECHAS (SOLO FECHAS)
                    // (Aquí NO va nada de facturas ni pagos)
                    
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

        $viewData = ['user' => $user, 'periods' => $periods];
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
        // 🚦 SISTEMA DE ALERTAS (Aquí SÍ usamos $factura)
        // ==========================================================
        $alertasVencimiento = [];

        if (!$isAdmin) {
            // Buscamos facturas vivas
            $facturasPorVencer = Billing::with('payments') 
                ->where('user_id', $user->id)
                ->whereIn('status', ['Pendiente', 'Abonado'])
                ->whereDate('fecha_vencimiento', '>=', Carbon::today())
                ->whereDate('fecha_vencimiento', '<=', Carbon::today()->addDays(7))
                ->get();

            foreach ($facturasPorVencer as $factura) {
                
                // 1. CHEQUEO DE PAGOS (Aquí sí va)
                $totalPagado = $factura->payments->sum('monto');
                if ($totalPagado >= $factura->monto) {
                    continue; // Si ya pagó, saltamos
                }

                $fechaVencimiento = Carbon::parse($factura->fecha_vencimiento)->startOfDay();
                $diasRestantes = Carbon::today()->diffInDays($fechaVencimiento, false);

                $mensaje = '';
                $titulo = '';
                $tipo = 'info';
                $agregarAlerta = false;

                // Niveles de alerta
                // 🔵 NIVEL 1: RELAJADO (7 a 3 días antes)
                if ($diasRestantes <= 7 && $diasRestantes >= 3) {
                    $titulo = "📅 Recordatorio Amigable";
                    $mensaje = "Hola {$user->nombre}, te recordamos que tu factura '{$factura->concepto}' vence en {$diasRestantes} " . ($diasRestantes === 1 ? "día." : "días.");
                    $tipo = 'info';
                    $agregarAlerta = true;
                }

                // 🟡 NIVEL 2: ATENCIÓN (2 días antes hasta 2 días después) — mismo rango,
                // pero con mensaje adaptado a antes/hoy/ya venció
                elseif ($diasRestantes <= 2 && $diasRestantes >= -2) {

                    $tipo = 'warning';
                    $agregarAlerta = true;

                    if ($diasRestantes > 1) {
                        // 2 o más días antes
                        $titulo = "⚠️ Atención: Pago Próximo";
                        $mensaje = "Hola {$user->nombre}, faltan {$diasRestantes} días para que venza tu factura '{$factura->concepto}'.";
                    } elseif ($diasRestantes === 1) {
                        $titulo = "⚠ Atención: Pago Próximo";
                        $mensaje = "Hola {$user->nombre}, falta 1 día para que venza tu factura '{$factura->concepto}'.";
                    } elseif ($diasRestantes === 0) {
                        $titulo = "⚠️ Atención: Vence Hoy";
                        $mensaje = "Hola {$user->nombre}, tu factura '{$factura->concepto}' vence HOY. Por favor realiza el pago.";
                    } elseif ($diasRestantes === -1) {
                        $titulo = "⚠️ Atención: Su Pago Venció";
                        $mensaje = "Hola {$user->nombre}, tu factura '{$factura->concepto}' venció ayer (hace 1 día). Por favor regulariza el pago.";
                    } else { // $diasRestantes === -2
                        $titulo = "⚠️ Atención: Su Pago Venció";
                        $mensaje = "Hola {$user->nombre}, tu factura '{$factura->concepto}' venció hace 2 días. Por favor regulariza el pago.";
                    }
                }

                // 🔴 NIVEL 3: URGENTE (3 a 7 días después del vencimiento)
                elseif ($diasRestantes <= -3 && $diasRestantes >= -7) {
                    $titulo = "⛔ AVISO URGENTE DE BAJA";
                    $abs = abs($diasRestantes);
                    $mensaje = "Tu factura '{$factura->concepto}' venció hace {$abs} " . ($abs === 1 ? "día. " : "días. ") .
                            "Si no se recibe el pago pronto, se procederá a la baja.";
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

        $periodo = Period::find($validated['period_id']);
        $fechaFactura = Carbon::parse($validated['fecha']);
        
        if ($periodo->start_date && $periodo->end_date) {
            $inicio = Carbon::parse($periodo->start_date)->startOfDay();
            $fin = Carbon::parse($periodo->end_date)->endOfDay();

            if (!$fechaFactura->between($inicio, $fin)) {
                return redirect()->back()
                    ->withErrors(['fecha' => "La fecha no coincide con el periodo."])
                    ->withInput();
            }
        } 

        $filePath = $request->file('archivo')->store('facturas', 'public');
        $xmlPath = null;
        
        if ($request->hasFile('archivo_xml')) {
            $xmlPath = $request->file('archivo_xml')->store('facturas_xml', 'public');
        }
        
        $lastId = Billing::withTrashed()->max('id') ?? 0;
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

        $url = route('Facturacion.index');
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
        
        $url = route('Facturacion.index');
        $anchor = '#factura-target-user-' . $userId . '-period-' . $periodId;

        return redirect()->to($url . $anchor)->with('success', 'Factura eliminada exitosamente.');
    }
}