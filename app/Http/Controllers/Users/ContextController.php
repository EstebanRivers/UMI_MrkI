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
        
       
        $availableContexts = $user->getAvailableRoles();

       if (empty($availableContexts)) {
        Log::warning('Usuario sin roles asignados o activos intentó acceder', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        Auth::logout();

        // Lo patea de vuelta al login con tu mensaje
        return redirect('/login')->withErrors([
            'email' => 'Usuario sin accesos a la plataforma. Contacte al administrador.'
        ]);
    }
        
        $activeContext = null;

        
        if ($institutionId && $roleId) {
            
            $activeContext = collect($availableContexts)->first(function ($context) use ($institutionId, $roleId) {
                return $context['institution_id'] == $institutionId && $context['role_id'] == $roleId;
            });

            
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

            if (!$activeContext['is_active']) {
                Log::warning('Intento de acceso a contexto deshabilitado', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'institution_id' => $institutionId,
                    'role_id' => $roleId,
                ]);

                
                Auth::logout();
                return redirect('/login')->withErrors([
                    'email' => 'Tu acceso a esta institución ha sido deshabilitado.'
                ]);
            }

            }
        } 
       
        elseif ($roleId) {
            $activeContext = collect($availableContexts)->firstWhere('role_id', $roleId);
            
           
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
        
        else {
            $activeContext = $availableContexts[0];
        }

        
        if (!$activeContext) {
            Log::error('Error al establecer contexto - contexto nulo', [
                'user_id' => $user->id,
                'available_contexts' => $availableContexts
            ]);
            
            return redirect()->route('dashboard')->withErrors([
                'error' => 'Error al establecer el contexto. Intenta nuevamente.'
            ]);
        }

       
        $request->session()->put('active_institution_id', $activeContext['institution_id']);
        $request->session()->put('active_role_id', $activeContext['role_id']);
        $request->session()->put('active_role_name', $activeContext['role_name']);
        $request->session()->put('active_institution_name', $activeContext['institution_name']);
        $request->session()->put('active_role_display_name', $activeContext['display_name']);
        $request->session()->put('active_institution_logo', $activeContext['logo_path']);
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
        
        
        return redirect()->route('dashboard');
    }
}
