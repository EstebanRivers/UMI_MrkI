<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContextController;

// --- RUTAS DEL CONTEXTO ACTIVO (Requieren estar autenticado) ---
Route::middleware(['auth'])->group(function () {
    // 1. Ruta para establecer el contexto inicial al iniciar sesión (GET/POST)
    // No requiere {roleId} aquí, el controlador toma el rol por defecto.
    Route::match(['get', 'post'], '/set-context', [ContextController::class, 'setContext'])
        ->name('context.set');

    // 2. Ruta para que el usuario pueda cambiar de rol (desde un botón en el sidebar, etc.)
    // Requiere el ID del rol al que quiere cambiar.
    Route::match(['get', 'post'], '/switch-role/{roleId}', [ContextController::class, 'setContext'])
        ->name('context.switch');
});
