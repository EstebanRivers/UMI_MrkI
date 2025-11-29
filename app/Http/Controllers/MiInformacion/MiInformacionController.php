<?php

namespace App\Http\Controllers\MiInformacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MiInformacionController extends Controller
{
    /**
     * Muestra el Perfil del usuario.
     * Accesible para TODOS (Master, Admin, Docente, Alumno).
     */
    public function index()
    {
        // 1. Obtenemos al usuario logueado
        $user = Auth::user();
        
        // 2. Cargamos sus relaciones (Perfiles y Dirección si existe)
        // (Asegúrate de tener estas relaciones en tu modelo User si las vas a usar)
        $user->load(['academicProfile', 'corporateProfile', 'address']);

        // 3. Enviamos la variable $user a la vista
        return view('layouts.MiInformacion.index', compact('user'));
    }

    /**
     * Muestra las Clases del alumno o docente.
     */
    public function showClases()
    {
        // 1. Obtener usuario
        $user = Auth::user(); 

        // 2. Obtener clases (simulado o real)
        $clases = []; // O $user->courses;
        
        // 3. Enviar AMBAS variables: usuario y clases
        return view('layouts.MiInformacion.clases', compact('user', 'clases'));
    }

    /**
     * Muestra el Horario.
     */
    public function showHorario()
    {
        $user = Auth::user();

        // TODO: Aquí harás la consulta para traer el horario
        
        return view('layouts.MiInformacion.horario');
    }

    /**
     * Muestra el Historial Académico.
     */
    public function showHistorial()
    {
        $user = Auth::user();
        
        // SIMULACIÓN DE DATOS (Historial)
        // Cuando tengas la base de datos llena, esto vendrá de: $user->historial
        $semestres = [
            (object)[
                'numero' => 1,
                'periodo' => 'AGO 2024 - ENE 2025',
                'promedio' => 9.6,
                'materias' => [
                    (object)['nombre' => 'Inteligencia de Negocios', 'creditos' => 5, 'calificacion' => 100, 'evaluacion' => 'OR', 'observaciones' => '-'],
                    (object)['nombre' => 'Ética Profesional', 'creditos' => 4, 'calificacion' => 95, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                ]
            ],
            (object)[
                'numero' => 2,
                'periodo' => 'FEB 2025 - JUL 2025',
                'promedio' => 9.2,
                'materias' => [
                    (object)['nombre' => 'Programación Web', 'creditos' => 5, 'calificacion' => 92, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                    (object)['nombre' => 'Redes de Computadoras', 'creditos' => 5, 'calificacion' => 90, 'evaluacion' => 'ORD', 'observaciones' => '-'],
                ]
            ]
        ];

        // Enviamos $user y $semestres a la vista
        return view('layouts.MiInformacion.historial', compact('user', 'semestres'));
    }
}