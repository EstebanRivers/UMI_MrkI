<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\ContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;


class LoginController extends Controller
{
    
    //Mostrar el formulario de login
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    //Procesar el login
    public function login(Request $request)
    {
     // 1. Validación: Quitamos la regla 'email' para permitir RFCs
        $request->validate([
            'login'    => ['required', 'string'], // Usamos 'login' como nombre genérico
            'password' => ['required'],
        ], [
            'login.required' => 'Debes ingresar tu RFC o Matrícula.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $input = $request->input('login');

        // 2. Buscamos al usuario por RFC o Matrícula
        // (Ya no buscamos por email)
        $user = User::where('RFC', $input)
                    ->orWhereHas('academicProfile', function ($query) use ($input) {
                        $query->where('matricula', $input);
                    })
                    ->first();

        // 3. Verificamos la contraseña
        if ($user && Hash::check($request->input('password'), $user->password)) {
            
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // 4. Preferencia de Contexto (Inteligente)
            if ($user->academicProfile && $input === $user->academicProfile->matricula) {
                session(['context_preference' => 'university']);
            } elseif ($input === $user->RFC) {
                session(['context_preference' => 'corporate']);
            }

            // Redirección
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('context.set')
                ]);
            }
            return redirect()->to(route('context.set'));
        }

        // 5. Si falla, enviamos error
        throw ValidationException::withMessages([
            'login' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
}
    
    

    
    //Cerrar sesión
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
