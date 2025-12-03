<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // <-- Importación necesaria para usar transacciones

class PaymentController extends Controller
{
    /**
     * Almacena un nuevo abono (pago parcial) para una factura.
     */
    public function store(Request $request)
    {
        $loggedInUser = Auth::user();

        // 1. Verificar Permisos
        if (!$loggedInUser->hasActiveRole('master') && !$loggedInUser->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        $validated = $request->validate([
            'billing_id' => 'required|exists:billings,id',
            'monto_abono' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'nota_abono' => 'nullable|string|max:255',
        ]);

        // FIX 1: Cargar la relación 'payments' (con with) para garantizar el cálculo correcto
        // Usamos findOrFail para una recuperación robusta.
        $billing = Billing::with('payments')->findOrFail($validated['billing_id']);
        
        // Calcular deuda actual
        $totalPagadoAntes = $billing->payments->sum('monto');
        $saldoPendiente = $billing->monto - $totalPagadoAntes;

        // Validación: No pagar de más
        if (round($validated['monto_abono'], 2) > round($saldoPendiente, 2)) {
            return redirect()->back()->withErrors(['monto_abono' => 'El monto excede el saldo pendiente.']);
        }

        try {
            // FIX 2: Usamos la fachada DB importada
            DB::beginTransaction();

            // 2. CREAR EL PAGO
            Payment::create([
                'billing_id' => $validated['billing_id'],
                'user_id' => $loggedInUser->id, // El usuario que registra el pago (el administrador)
                'monto' => $validated['monto_abono'],
                'fecha_pago' => $validated['fecha_pago'],
                'nota' => $validated['nota_abono'],
                'metodo_pago' => null, // Añadimos esto para ser explícitos y evitar fallos si el campo no es nullable en BD
            ]);

            // 3. ACTUALIZAR EL ESTATUS DE LA FACTURA
            $nuevoTotalPagado = $totalPagadoAntes + $validated['monto_abono'];

            // Usamos comparación estricta de decimales para determinar el estado 'Pagada'.
            if (round($nuevoTotalPagado, 2) >= round($billing->monto, 2)) {
                $billing->status = 'Pagada'; 
            } else {
                $billing->status = 'Abonado';
            }

            $billing->save(); 

            DB::commit(); // FIX 2: Commit usando la fachada DB

            // 4. Redirección
            $url = route('Facturacion.index');
            // Ancla para regresar al acordeón exacto
            $anchor = '#factura-target-user-' . $billing->user_id . '-period-' . $billing->period_id;

            return redirect()->to($url . $anchor)->with('success', 'Pago registrado y estatus actualizado.');

        } catch (\Exception $e) {
            DB::rollBack(); // FIX 2: Rollback usando la fachada DB
            Log::error('Error al registrar abono: ' . $e->getMessage());
            // Mostramos un mensaje de error claro en pantalla
            return redirect()->back()->withErrors(['msg' => 'Error interno al guardar el abono. Por favor, revise el log para más detalles.']);
        }
    }
}