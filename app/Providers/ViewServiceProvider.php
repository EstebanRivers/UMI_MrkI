<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usamos un View Composer para compartir datos con el layout principal
        View::composer('layouts.app', function ($view) {
            // Verificamos si el usuario está autenticado
            if (Auth::check()) {
                // Si lo está, obtenemos sus contextos y los pasamos a la vista
                $view->with('availableContexts', Auth::user()->getAvailableRoles());
            } else {
                // Si no, pasamos un array vacío para evitar errores
                $view->with('availableContexts', []);
            }
        });
    }
}