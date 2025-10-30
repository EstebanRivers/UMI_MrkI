<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Users\User;

class MiInformacionController extends Controller
{
    /**
     * Muestra la vista de la sección solicitada (Perfil, Clases, Horarios, etc.).
     * * @param string $seccion La clave de la sección a mostrar (ej: 'perfil', 'clases').
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($seccion = 'perfil')
    {
        // Mapeo de secciones permitidas y sus títulos (para el encabezado de la página)
        $allowedSections = [
            'perfil' => 'PERFIL',
            'clases' => 'CLASES',
            'horarios' => 'HORARIOS',
            'historial-academico' => 'HISTORIAL ACADÉMICO',
        ];

        // 1. Validar la sección solicitada.
        if (!array_key_exists($seccion, $allowedSections)) {
            // Si la sección no existe, redirigimos a 'perfil' (comportamiento por defecto)
            return redirect()->route('MiInformacion.show', ['seccion' => 'perfil']);
        }

        // 2. Preparar los datos y la ruta de la subvista
        $user = Auth::user();
        $page_title = $allowedSections[$seccion];
        // La vista a cargar será dinámica: layouts.MiInformacion.partials.perfil, etc.
        $subviewPath = "layouts.MiInformacion.partials.{$seccion}"; 

        // Puedes agregar lógica para cargar datos específicos para cada sección aquí.
        // Por ahora, solo pasamos el usuario autenticado y el título.
        $data = [
            'page_title' => $page_title,
            'user' => $user,
            // Agregamos la ruta de la subvista a cargar por el index.blade.php
            'subview' => $subviewPath, 
            'seccion' => $seccion // Para que se pueda usar en el layout y en el menú.
        ];

        // 3. Retornar la vista principal de MiInformacion, que incluirá la subvista.
        // La vista principal (index.blade.php) deberá usar la variable $subview.
        return view('layouts.MiInformacion.index', $data);
       
    }
}