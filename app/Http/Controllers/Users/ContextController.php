<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ContextController extends Controller
{
    /**
     * Establece el contexto inicial o cambia el contexto activo del usuario.
     */
    public function setContext(Request $request, int $roleId = null)
    {
        $user = Auth::user();
        
        // 1. Obtener todos los contextos disponibles para el usuario
        $availableContexts = $user->getAvailableRoles();

        if (empty($availableContexts)) {
            Auth::logout();
            return redirect('/login')->withErrors(['error' => 'No tienes roles asignados.']);
        }
        
        $activeContext = null;

        if ($roleId) {
            // 2. Intentar encontrar el contexto solicitado por el usuario (desde el botón)
            $activeContext = collect($availableContexts)->firstWhere('role_id', $roleId);
        }

        if (!$activeContext) {
            // 3. Si no se especificó o no es válido, establecer el contexto por defecto (el primer rol que encuentre)
            $activeContext = $availableContexts[0];
        }

        // 4. Guardar el contexto activo en la sesión
        $request->session()->put('active_institution_id', $activeContext['institution_id']);
        $request->session()->put('active_role_id', $activeContext['role_id']);
        $request->session()->put('active_role_name', $activeContext['role_name']);
        $request->session()->put('active_institution_name', $activeContext['institution_name']);

        // 5. Redirigir al dashboard
        return redirect()->route('dashboard.index');
    }
}
