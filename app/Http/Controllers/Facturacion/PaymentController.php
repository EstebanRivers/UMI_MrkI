<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facturacion\Billing;
use App\Models\Facturacion\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Almacena un nuevo abono (pago parcial) para una factura.
     */
    public function store(Request $request)
    {
        $loggedInUser = Auth::user();

        // REGLA: Solo Master o Control Admin pueden registrar abonos
        if (!$loggedInUser->hasActiveRole('master') && !$loggedInUser->hasActiveRole('control_administrativo')) {
            abort(403, 'ACCIÓN NO AUTORIZADA.');
        }

        $validated = $request->validate([
            'billing_id' => 'required|exists:billings,id',
            'monto_abono' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date',
            'nota_abono' => 'nullable|string|max:255',
        ]);

        $billing = Billing::find($validated['billing_id']);
        $totalPagado = $billing->payments->sum('monto');
        $saldoPendiente = $billing->monto - $totalPagado;

        // No permitir pagar más de lo que se debe
        if ($validated['monto_abono'] > $saldoPendiente) {
            return redirect()->back()->withErrors(['monto_abono' => 'El monto a abonar no puede ser mayor que el saldo pendiente.']);
        }

        try {
            Payment::create([
                'billing_id' => $validated['billing_id'],
                'user_id' => $loggedInUser->id, // El admin que registra el pago
                'monto' => $validated['monto_abono'],
                'fecha_pago' => $validated['fecha_pago'],
                'nota' => $validated['nota_abono'],
            ]);

            return redirect()->route('Facturacion.index')->with('success', 'Abono registrado exitosamente.');

        } catch (\Exception $e) {
            Log::error('Error al registrar abono: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Error interno al guardar el abono.']);
        }
    }
}
