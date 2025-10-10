<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Models\Users\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ContextController extends Controller
{
    /**
     * Establece el contexto inicial o cambia el contexto activo del usuario.
     */
    public function setContext(Request $request, int $institutionId = null, int $roleId = null)
    {
        $user = Auth::user();
        
        // 1. Obtener todos los contextos disponibles para el usuario
        $availableContexts = $user->getAvailableRoles();

        if (empty($availableContexts)) {
            Log::warning('Usuario sin roles asignados intentó acceder', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            Auth::logout();
            return redirect('/login')->withErrors(['error' => 'No tienes roles asignados.']);
        }
        
        $activeContext = null;

         // 2. Si se proporcionaron institutionId y roleId, validar que el usuario tenga acceso
        if ($institutionId && $roleId) {
            // Buscar el contexto EXACTO solicitado
            $activeContext = collect($availableContexts)->first(function ($context) use ($institutionId, $roleId) {
                return $context['institution_id'] == $institutionId && $context['role_id'] == $roleId;
            });

            // VALIDACIÓN DE SEGURIDAD: Si no existe, es un intento de acceso no autorizado
            if (!$activeContext) {
                Log::warning('Intento de acceso no autorizado a contexto', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'attempted_institution_id' => $institutionId,
                    'attempted_role_id' => $roleId,
                    'available_contexts' => $availableContexts
                ]);

                return redirect()->route('dashboard')->withErrors([
                    'error' => 'No tienes autorización para acceder a ese rol/institución.'
                ]);
            }
        } 
        // 3. Si solo se proporcionó roleId (compatibilidad con código antiguo)
        elseif ($roleId) {
            $activeContext = collect($availableContexts)->firstWhere('role_id', $roleId);
            
            // VALIDACIÓN: Verificar que el rol exista en contextos disponibles
            if (!$activeContext) {
                Log::warning('Intento de acceso a rol no autorizado', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'attempted_role_id' => $roleId,
                    'available_roles' => array_column($availableContexts, 'role_id')
                ]);

                return redirect()->route('dashboard')->withErrors([
                    'error' => 'No tienes autorización para ese rol.'
                ]);
            }
        }
        // 4. Si no se especificó contexto, usar el primero disponible (login inicial)
        else {
            $activeContext = $availableContexts[0];
        }

        // 5. Validación final: Verificar que se seleccionó un contexto válido
        if (!$activeContext) {
            Log::error('Error al establecer contexto - contexto nulo', [
                'user_id' => $user->id,
                'available_contexts' => $availableContexts
            ]);
            
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error al establecer el contexto. Intenta nuevamente.'
            ]);
        }

        // 4. Guardar el contexto activo en la sesión
        $request->session()->put('active_institution_id', $activeContext['institution_id']);
        $request->session()->put('active_role_id', $activeContext['role_id']);
        $request->session()->put('active_role_name', $activeContext['role_name']);
        $request->session()->put('active_institution_name', $activeContext['institution_name']);
        $request->session()->put('active_role_display_name', $activeContext['display_name']);
        
        Log::info('Cambio de contexto exitoso', [
            'user_id' => $user->id,
            'email' => $user->email,
            'institution_id' => $activeContext['institution_id'],
            'institution_name' => $activeContext['institution_name'],
            'role_id' => $activeContext['role_id'],
            'role_name' => $activeContext['role_name'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // 5. Redirigir al dashboard
        return redirect()->route('dashboard');
    }
}
