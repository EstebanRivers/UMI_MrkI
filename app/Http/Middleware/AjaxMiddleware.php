<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AjaxMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pasa la petición al controlador PRIMERO
        $response = $next($request);

        // Ahora, si es una petición AJAX, mejoramos la respuesta
        if ($request->ajax()) {
            if (!Auth::check()) {
                return response()->json(['redirect' => route('login')], 401);
            }
            
            // Añadimos tus headers de seguridad
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        return $response;
    }
}