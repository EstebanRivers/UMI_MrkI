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

        $billing = Billing::find($validated['billing_id']);
        
        // Calcular deuda actual
        $totalPagadoAntes = $billing->payments->sum('monto');
        $saldoPendiente = $billing->monto - $totalPagadoAntes;

        // Validación: No pagar de más
        // Usamos round para evitar problemas de decimales (ej. 4999.99999)
        if (round($validated['monto_abono'], 2) > round($saldoPendiente, 2)) {
            return redirect()->back()->withErrors(['monto_abono' => 'El monto excede el saldo pendiente.']);
        }

        try {
            // Iniciar Transacción (Para que se guarden ambos o ninguno)
            \DB::beginTransaction();

            // 2. CREAR EL PAGO
            Payment::create([
                'billing_id' => $validated['billing_id'],
                'user_id' => $loggedInUser->id,
                'monto' => $validated['monto_abono'],
                'fecha_pago' => $validated['fecha_pago'],
                'nota' => $validated['nota_abono'],
            ]);

            // 3. ACTUALIZAR EL ESTATUS DE LA FACTURA (¡ESTO FALTABA!)
            // Recalculamos el total sumando el nuevo pago
            $nuevoTotalPagado = $totalPagadoAntes + $validated['monto_abono'];

            // Margen de error de 1 peso por posibles decimales flotantes
            if ($nuevoTotalPagado >= ($billing->monto - 1)) {
                $billing->status = 'Pagada'; 
            } else {
                $billing->status = 'Abonado';
            }

            $billing->save(); // Guardamos el cambio en la tabla 'billings'

            \DB::commit();

            // 4. Redirección
            $url = route('Facturacion.index');
            // Ancla para regresar al acordeón exacto
            $anchor = '#factura-target-user-' . $billing->user_id . '-period-' . $billing->period_id;

            return redirect()->to($url . $anchor)->with('success', 'Pago registrado y estatus actualizado.');

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error al registrar abono: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Error interno al guardar el abono.']);
        }
    }
}
