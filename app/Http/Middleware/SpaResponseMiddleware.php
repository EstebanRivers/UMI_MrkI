<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SpaResponseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si es una petición AJAX y la respuesta es una vista completa...
        if ($request->ajax() && $response instanceof \Illuminate\View\View) {
            // ...la convertimos a solo el contenido HTML renderizado.
            return response($response->render());
        }

        return $response;
    }
}