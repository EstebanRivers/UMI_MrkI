<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\ContextController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


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
      $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ], [
        'email.required' => 'El campo email es obligatorio.',
        'email.email' => 'El formato del email no es válido.',
        'password.required' => 'El campo contraseña es obligatorio.',
    ]);

    // Verificar si es una petición AJAX
    $isAjax = $request->ajax() || $request->wantsJson();

    // 1. Intenta la autenticación (¡Este es el único trabajo de este archivo!)
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        
        // 2. Si la contraseña es correcta, continúa al siguiente paso
        $request->session()->regenerate();
        
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'redirect' => route('context.set') // Te manda al ContextController
            ]);
        }
        
        return redirect()->to(route('context.set'));
    }

    // 3. Si las credenciales fallaron (sin cambios)
    if ($isAjax) {
        return response()->json([
            'success' => false,
            'errors' => ['email' => 'Las credenciales no coinciden con nuestros registros.']
        ], 422);
    }

    throw ValidationException::withMessages([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
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
