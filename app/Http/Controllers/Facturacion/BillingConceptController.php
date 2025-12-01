<?php

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\BillingConcept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingConceptController extends Controller
{
        public function index(Request $request)
    {
        $institutionId = session('active_institution_id');
        $query = BillingConcept::where('institution_id', $institutionId);

        // Búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('concept', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Esto trae TODOS los registros y soluciona que no veas los demás.
        $conceptos = $query->orderBy('created_at', 'desc')->get(); 

        $page_title = 'Conceptos y Montos de Facturación';
        
        return view('layouts.Facturacion.conceptos.index', compact('conceptos', 'page_title'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'concept' => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
        ]);

        try {
            BillingConcept::create([
                'institution_id' => session('active_institution_id'),
                'concept'        => $request->concept,
                'amount'         => $request->amount,
                'description'    => $request->description,
                'is_active'      => $request->has('is_active') ? 1 : 0,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Guardado correctamente']);
            }
            // Redirige a la ruta INDEX (que usa el controlador corregido arriba)
            return redirect()->route('facturacion.conceptos.index')->with('success', 'Concepto creado.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error en servidor'], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'concept' => 'required|string|max:255',
            'amount'  => 'required|numeric|min:0',
        ]);

        try {
            $concept = BillingConcept::findOrFail($id);
            
            $concept->update([
                'concept'     => $request->concept,
                'amount'      => $request->amount,
                'description' => $request->description,
                'is_active'   => $request->has('is_active') ? 1 : 0,
            ]);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Actualizado correctamente']);
            }
            return redirect()->route('facturacion.conceptos.index')->with('success', 'Concepto actualizado.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Error en servidor'], 500);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $concept = BillingConcept::findOrFail($id);
        $concept->delete();
        return redirect()->route('facturacion.conceptos.index')->with('success', 'Eliminado correctamente');
    }

    public function toggleStatus($id)
    {
        $concept = BillingConcept::findOrFail($id);
        $concept->is_active = !$concept->is_active;
        $concept->save();
        return redirect()->route('facturacion.conceptos.index')->with('success', 'Estatus actualizado');
    }
}