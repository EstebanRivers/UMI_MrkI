<?php

namespace App\Http\Controllers\AdmonCont;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users\Career;
use App\Models\Users\User;
use App\Models\AdmonCont\Materia;
use App\Models\AdmonCont\HorarioClase;
use App\Models\AdmonCont\HorarioFranja;
use App\Models\AdmonCont\Facility;


class HorarioController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtener los datos necesarios para los desplegables
        $carreras = Career::all();
        $aulas = Facility::all();
        $horarios = HorarioClase::with(['carrera', 'materia', 'user', 'aula'])->get();
        $query = HorarioClase::with(['carrera', 'materia', 'user', 'aula', 'franjas']);
        $search = $request->search_query;

        // 💡 Importante: Filtramos los usuarios para que solo sean docentes.
        // Asumiendo que tienes un campo 'role' o una tabla de roles
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'docente'); // Asumiendo que el campo 'name' del Role es 'docente'
        })->get(); 
        
        // Las materias se cargan normalmente. 
        // Nota: Si dependes de la carrera seleccionada, esta lista se cargará inicialmente vacía o con AJAX.
        $materias = Materia::all(); 
        
        // 💡 CONDICIÓN CORREGIDA: Solo aplicamos el filtro si hay contenido útil.
        if ($search !== null && $search !== '') { 
            
            // Usamos una Cláusula WHERE principal para agrupar todas las condiciones OR
            $query->where(function ($q) use ($search) {
                
                // 1. Buscar por Materia
                $q->whereHas('materia', function ($sq) use ($search) {
                    // Usamos el método where(columna, operador, valor) para mayor claridad
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                })
                
                // 2. Buscar por Carrera
                ->orWhereHas('carrera', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', '%' . $search . '%');
                })
                
                // 3. Buscar por Docente
                ->orWhereHas('user', function ($sq) use ($search) {
                    $sq->where('nombre', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $horarios = $query->get();
        // 2. ¿Qué necesitamos hacer ahora con estos datos ($carreras, $aulas, $docentes, $materias)?
        return view('layouts.ControlAdmin.Horarios.index', [
            // Aquí van tus variables
            // 1. carreras
            'carreras' => $carreras,
            // 2. materias
            'materias' => $materias,
            // 3. docentes
            'docentes' => $docentes,
            // 4. aulas
            'aulas' => $aulas,
            // 5. Horarios
            'horarios' => $horarios
        ]);
    }
    
    public function store(Request $request){
        // 1. VALIDACIÓN
        $request->validate([
            'materia_id' => 'required|exists:materias,id',
            // 1             2        3      4
            'carrera_id' => 'required|exists:carrers,id',
            'docente_id' => 'required|exists:users,id',
            'aula_id'    => 'required|exists:facilities,id',
            'franjas_json' => 'required|json', // Aseguramos que la data venga
        ]);
        
        // Decodificar las franjas (el array temporal de JS)
        $franjasData = json_decode($request->franjas_json, true);
        
        //dd($request->all(), $franjasData);
        // ¡Validación crítica! Asegurar que se haya añadido al menos una franja de tiempo
        if (empty($franjasData)) {
            return redirect()->back()->withErrors(['franjas_json' => 'Debe añadir al menos una franja horaria.']);
        }

        // Usamos una transacción para asegurar que, si falla el guardado de una franja, 
        // se revierta el guardado del registro maestro.
        DB::beginTransaction();
        try {
            // 2. CREAR REGISTRO MAESTRO (HorarioClase)
            $horarioClase = HorarioClase::create([
                'materia_id' => $request->materia_id,
                'carrera_id' => $request->carrera_id,
                'user_id'    => $request->docente_id,
                'aula_id'    => $request->aula_id,
            ]);
            
            // 3. PREPARAR Y GUARDAR FRANJAS RELACIONADAS
            $franjasAGuardar = [];

            foreach ($franjasData as $franja) {
                // Generamos un registro individual en la tabla horario_franjas por cada día
                foreach ($franja['dias_semana'] as $dia) {
                    $franjasAGuardar[] = [
                        'dias_semana'  => $dia,
                        'hora_inicio' => $franja['hora_inicio'],
                        'hora_fin'    => $franja['hora_fin'],
                    ];
                }
            }

            // Usamos saveMany() para guardar todas las filas de franjas de golpe
            $horarioClase->franjas()->createMany($franjasAGuardar); 
            
            DB::commit();

            return redirect()->route('Horarios.index')->with('success', 'Horario creado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();

            // 🚨 ESTO ES LO QUE DEBE PERMANECER PARA EL DIAGNÓSTICO 🚨
            dd('ERROR DE BASE DE DATOS:', $e->getMessage(), 'LÍNEA DE CÓDIGO:', $e->getLine());
            // Manejo de errores
            //return redirect()->back()->withInput()->withErrors(['error' => 'Error al guardar el horario.']);

            //dd($e->getMessage(), $e->getLine());
            
        }
    }
    public function destroy(HorarioClase $horario){
        // 💡 El Route Model Binding pasa directamente el objeto HorarioClase
        
        try {
            $horario->delete(); // Elimina el registro maestro
            
            // La configuración 'onDelete('cascade')' en tu migración se encarga 
            // de borrar automáticamente todas las filas de horario_franjas relacionadas.

            return redirect()->route('Horarios.index')->with('success', 'Horario eliminado correctamente.');
            
        } catch (\Exception $e) {
            // Maneja cualquier error de base de datos
            return redirect()->route('Horarios.index')->withErrors(['error' => 'No se pudo eliminar el horario.']);
        }
    }
    public function edit(HorarioClase $horario){
        // Cargar listas
        $carreras = Career::all();
        $aulas = Facility::all();
        $horario->load('franjas');

        // 💡 Importante: Filtramos los usuarios para que solo sean docentes.
        // Asumiendo que tienes un campo 'role' o una tabla de roles
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'docente'); // Asumiendo que el campo 'name' del Role es 'docente'
        })->get(); 
        
        // Las materias se cargan normalmente. 
        // Nota: Si dependes de la carrera seleccionada, esta lista se cargará inicialmente vacía o con AJAX.
        $materias = Materia::all(); 
        // Cargar las franjas horarias y el contador
        $franjas_json = json_encode($horario->franjas->toArray());
        
        // NOTA: Para simplificar, asumiremos que tu tabla de horarios también se carga aquí
        $horarios = HorarioClase::with(['carrera', 'materia', 'user', 'aula', 'franjas'])->get();

        // 💡 PASAMOS EL OBJETO $horario A LA VISTA INDEX.
        return view('layouts.ControlAdmin.Horarios.index', compact(
            // 1. carreras
            'carreras',
            // 2. materias
            'materias',
            // 3. docentes
            'docentes',
            // 4. aulas
            'aulas',
            // 5. Horarios
            'horarios',
            // 6. Franjas JSON
            'franjas_json',
            // 7. Horario
            'horario'
        ));
    }
    public function update(Request $request, HorarioClase $horario){
    // 1. VALIDACIÓN
    $request->validate([
        // Los campos bloqueados (carrera_id, materia_id) se reciben del input hidden
        'materia_id' => 'required|exists:materias,id', 
        'carrera_id' => 'required|exists:carrers,id',   
        
        // Campos editables
        'docente_id' => 'required|exists:users,id',
        'aula_id'    => 'required|exists:facilities,id',
        'franjas_json' => 'required|json', 
    ]);
    
    // Decodificar las franjas (el array temporal de JS)
    $franjasData = json_decode($request->franjas_json, true);
    
    if (empty($franjasData)) {
        return redirect()->back()->withInput()->withErrors(['franjas_json' => 'Debe añadir al menos una franja horaria.']);
    }

    DB::beginTransaction();
    try {
        // 2. ACTUALIZAR REGISTRO MAESTRO (HorarioClase)
        $horario->update([
            'materia_id' => $request->materia_id,
            'carrera_id' => $request->carrera_id,
            'user_id'    => $request->docente_id,
            'aula_id'    => $request->aula_id,
        ]);
        
        // 3. PREPARAR Y SINCRONIZAR FRANJAS HORARIAS
        $franjasAGuardar = [];

        foreach ($franjasData as $franja) {
            // Generamos un registro individual por cada día en el array del frontend
            foreach ($franja['dias_semana'] as $dia) {
                $franjasAGuardar[] = [
                    'dias_semana' => $dia,
                    'hora_inicio' => $franja['hora_inicio'],
                    'hora_fin'    => $franja['hora_fin'],
                ];
            }
        }

        // 🚨 Sincronización: Eliminar las franjas antiguas y crear las nuevas. 🚨
        $horario->franjas()->delete(); // Borra todas las franjas_horarias relacionadas (CASCADE)
        $horario->franjas()->createMany($franjasAGuardar); // Crea las nuevas

        DB::commit();

        return redirect()->route('Horarios.index')->with('success', 'Horario actualizado exitosamente!');

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Para depuración, puedes usar dd($e->getMessage()) aquí.
        return redirect()->back()->withInput()->withErrors(['error' => 'Error al actualizar el horario.']);
    }
}
}
