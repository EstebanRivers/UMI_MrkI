<?php

namespace App\Http\Controllers\Ajustes; // Asegúrate que el namespace sea correcto

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// --- IMPORTA TODOS LOS MODELOS ---
use App\Models\Users\Institution;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\Period;

class AjustesController extends Controller
{

    /**
     * Muestra la vista principal de la sección (tu index.blade.php).
     */
    public function show(Request $request, $seccion)
    {
        $search = $request->get('search');
        $query = $this->getQueryForSeccion($seccion, $search);

        if (!$query) {
            abort(404, 'Sección no encontrada');
        }

        // Aplicar filtros de autorización y de institución activa
        $this->applyAuthFilters($query, $seccion);

        // Paginar los resultados
        $data = $query->paginate(15)->withQueryString(); 
        
        $titles = $this->getSectionTitles($seccion);
        $page_title = $titles['plural']; // Título de la página
        $singular_title = $titles['singular']; // Para el botón "Agregar"

       return view('layouts.Ajustes.index', compact('data', 'seccion', 'page_title', 'singular_title'));
    }

    // -----------------------------------------------------------------
    // --- MÉTODOS PARA EL MODAL (NUEVOS) ---
    // -----------------------------------------------------------------

    /**
     * Devuelve el HTML del formulario de CREACIÓN para inyectar en el modal.
     */
    public function getCreateForm($seccion)
    {
        // Obtenemos los datos necesarios para los dropdowns (si aplica)
        $data = $this->getFormData($seccion);

        // Retornamos la vista parcial del formulario
        return view("layouts.Ajustes.forms._{$seccion}", $data);
    }

    /**
     * Devuelve el HTML del formulario de EDICIÓN con datos para inyectar en el modal.
     */
    public function getEditForm($seccion, $id)
    {
        // Obtenemos el item a editar y los datos para dropdowns
        $data = $this->getFormData($seccion, $id);

        if (!isset($data['item'])) {
            return response('Recurso no encontrado o no autorizado', 404);
        }

        // Retornamos la vista parcial del formulario, que se rellenará con $item
        return view("layouts.Ajustes.forms._{$seccion}", $data);
    }


    // -----------------------------------------------------------------
    // --- MÉTODOS CRUD (STORE, UPDATE, DESTROY) ---
    // -----------------------------------------------------------------

    /**
     * Almacena un nuevo registro desde el modal.
     */
    public function store(Request $request, $seccion)
    {
        $activeInstitutionId = session('active_institution_id');
        $data = $request->all();
        
        // Asignar la institution activa SOLO si el formulario NO la provee
        // (Para 'periods' y 'workstations')
        if (!isset($data['institution_id'])) {
             if ($seccion === 'workstations') {
                // Workstation obtiene la institution de su departamento padre
                $department = Department::find($request->department_id);
                $data['institution_id'] = $department ? $department->institution_id : $activeInstitutionId;
            } else if ($seccion !== 'institutions') {
                // Periods, etc., la obtienen de la sesión activa
                $data['institution_id'] = $activeInstitutionId;
            }
        }
        // Para 'departments' y 'users', $data['institution_id'] viene del formulario.

        switch ($seccion) {
            case 'institutions':
                $request->validate(['name' => 'required|string|max:255|unique:institutions']);
                if ($request->hasFile('logo_path')) {
                    $data['logo_path'] = $request->file('logo_path')->store('logos/institutions', 'public');
                }
                Institution::create($data);
                break;
            
            case 'departments':
                $request->validate([
                    'name' => 'required|string|max:255',
                    'institution_id' => 'required|exists:institutions,id'
                ]);
                Department::create($data);
                break;

            case 'workstations':
                 $request->validate([
                    'name' => 'required|string|max:255',
                    'department_id' => 'required|exists:departments,id'
                ]);
                // $data['institution_id'] fue asignada arriba
                Workstation::create($data);
                break;

            case 'periods':
                $request->validate([
                    'name' => 'required|string|max:255',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after:start_date',
                ]);
                
                // LÓGICA ESPECIAL: Desactivar otros periodos de esta institución
                Period::where('institution_id', $data['institution_id'])->update(['is_active' => false]);
                $data['is_active'] = true; // El nuevo siempre es activo
                
                // $data['institution_id'] fue asignada arriba
                Period::create($data);
                break;

            case 'users':
                // --- INICIO DE LA SECCIÓN CORREGIDA ---

                // 1. VALIDAR (Quitamos 'unique' de email y RFC)
                $validatedData = $request->validate([
                    'nombre' => 'required|string|max:255',
                    'apellido_paterno' => 'required|string|max:255',
                    'apellido_materno' => 'nullable|string|max:255',
                    'RFC' => ['required', 'string', 'max:13'], // Se quitó Rule::unique
                    'email' => ['required', 'email'],           // Se quitó Rule::unique
                    'password' => 'required|string|min:8|confirmed',
                    'role_id' => 'required|exists:roles,id',
                    'institution_id' => 'required|exists:institutions,id',
                    'department_id' => 'nullable|exists:departments,id',
                    'workstation_id' => 'nullable|exists:workstations,id',
                ]);
                
                // 2. BUSCAR O CREAR AL USUARIO
                $user = User::where('email', $validatedData['email'])->first();

                // Preparamos los datos del usuario para la tabla 'users'
                // (NOTA: quitamos 'institution_id' de esta lista)
                $userData = [
                    'nombre' => $validatedData['nombre'],
                    'apellido_paterno' => $validatedData['apellido_paterno'],
                    'apellido_materno' => $validatedData['apellido_materno'],
                    'RFC' => $validatedData['RFC'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'role_id' => $validatedData['role_id'], // Se asigna el rol global aquí
                    'department_id' => $validatedData['department_id'],
                    'workstation_id' => $validatedData['workstation_id'],
                ];
                
                $institution_id_to_add = $validatedData['institution_id'];
                $message = '';

                if (!$user) {
                    // --- Caso 1: Usuario NO existe ---
                    $user = User::create($userData);
                    $message = 'Nuevo usuario creado';
                } else {
                    // --- Caso 2: Usuario SÍ existe ---
                    // Actualizamos sus datos por si cambiaron
                    // (Omitimos 'password' para no sobrescribirla)
                    unset($userData['password']); 
                    $user->update($userData);
                    $message = 'Usuario existente actualizado';
                }

                // 3. ASIGNAR A LA INSTITUCIÓN (USANDO LA TABLA PIVOTE M-M)
                // (Asumimos que la relación se llama 'institutions' en tu modelo User)
                if ($user->institutions()->where('institution_id', $institution_id_to_add)->exists()) {
                    $message .= ', pero ya pertenecía a esta institución.';
                } else {
                    $user->institutions()->attach($institution_id_to_add);
                    $message .= ' y asignado a la nueva institución.';
                }

                // 4. LIMPIEZA DE LÓGICA DE ROLES
                // Eliminamos la línea conflictiva '$user->roles()->attach(...)'
                // El rol ya se asignó en el 'create' o 'update' de arriba.
                
                // Redirigir con el mensaje personalizado
                return redirect()->route('ajustes.show', ['seccion' => $seccion])
                                 ->with('success', $message);
                
                // break; // El 'return' de arriba hace innecesario el 'break'
                // --- FIN DE LA SECCIÓN CORREGIDA ---
            
            default:
                return back()->with('error', 'Sección no válida.');
        }

        return redirect()->route('ajustes.show', ['seccion' => $seccion])
                         ->with('success', 'Registro creado exitosamente.');
    }

    /**
     * Actualiza un registro existente desde el modal.
     */
    public function update(Request $request, $seccion, $id)
    {
        $item = $this->findItem($seccion, $id);
        if (!$item) return back()->with('error', 'Registro no encontrado.');

        switch ($seccion) {
            case 'institutions':
                $data = $request->all();
                $request->validate(['name' => 'required|string|max:255|unique:institutions,name,' . $id]);
                if ($request->hasFile('logo_path')) {
                    // Borrar logo anterior
                    if ($item->logo_path) Storage::disk('public')->delete($item->logo_path);
                    $data['logo_path'] = $request->file('logo_path')->store('logos/institutions', 'public');
                }
                $item->update($data);
                break;
            
            case 'departments':
                $data = $request->all();
                $request->validate([
                    'name' => 'required|string|max:255',
                    'institution_id' => 'required|exists:institutions,id'
                ]);
                $item->update($data);
                break;
            
            case 'workstations':
                $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'department_id' => 'required|exists:departments,id'
                ]);
                // Recalcular institution_id si el departamento cambia
                $department = Department::find($validatedData['department_id']);
                $validatedData['institution_id'] = $department ? $department->institution_id : $item->institution_id;
                $item->update($validatedData);
                break;

            case 'periods':
                 $validatedData = $request->validate([
                    'name' => 'required|string|max:255',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after:start_date',
                ]);
                $item->update($validatedData);
                break;
            
            case 'users':
                // !! VALIDACIÓN CORREGIDA !!
                $validatedData = $request->validate([
                    'nombre' => 'required|string|max:255',
                    'apellido_paterno' => 'required|string|max:255',
                    'apellido_materno' => 'nullable|string|max:255',
                    'RFC' => ['required', 'string', 'max:13', Rule::unique('users', 'RFC')->ignore($item->id)],
                    'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($item->id)],
                    'role_id' => 'required|exists:roles,id',
                    'institution_id' => 'required|exists:institutions,id',
                    'department_id' => 'nullable|exists:departments,id',
                    'workstation_id' => 'nullable|exists:workstations,id',
                ]);

                // Actualizar contraseña solo si se provee una nueva
                if (!empty($request->password)) {
                    $request->validate(['password' => 'string|min:8|confirmed']);
                    $validatedData['password'] = Hash::make($request->password);
                }
                
                // Remueve institution_id de validatedData antes de update()
                unset($validatedData['institution_id']);
                $item->update($validatedData);

                // Actualizar o sincronizar instituciones en la tabla pivote
                if (!empty($request->institution_id)) {
                    $item->institutions()->syncWithoutDetaching([$request->institution_id]);
                }

                // Actualizar rol si aplica
                $item->role_id = $validatedData['role_id'];
                $item->save();
                break;

            default:
                return back()->with('error', 'Sección no válida.');
        }

        return redirect()->route('ajustes.show', ['seccion' => $seccion])
                         ->with('success', 'Registro actualizado exitosamente.');
    }

    /**
     * Elimina un registro.
     */
    public function destroy($seccion, $id)
    {
        $item = $this->findItem($seccion, $id);
        if (!$item) return back()->with('error', 'Registro no encontrado.');
        
        // Autorización (¿puede este usuario borrar este item?)
        // ... $this->authorize('delete', $item); ...

        try {
            if ($seccion === 'institutions' && $item->logo_path) {
                Storage::disk('public')->delete($item->logo_path);
            }
            // Lógica de borrado en cascada (ej. usuarios y roles)
            if ($seccion === 'users') {
                $item->roles()->detach();
            }

            $item->delete();
            
            return redirect()->route('ajustes.show', ['seccion' => $seccion])
                             ->with('success', 'Registro eliminado exitosamente.');

        } catch (\Exception $e) {
            // Manejar errores de integridad (ej. borrar un depto con puestos)
            return back()->with('error', 'No se pudo eliminar el registro. Puede estar en uso.');
        }
    }


    // -----------------------------------------------------------------
    // --- MÉTODOS PRIVADOS DE AYUDA ---
    // -----------------------------------------------------------------

    /**
     * Obtiene el Query Builder base para una sección, aplicando búsqueda.
     */
    private function getQueryForSeccion($seccion, $search = null)
    {
        $activeInstitutionId = session('active_institution_id');
        $query = null;
        switch ($seccion) {
            case 'institutions':
                $query = Institution::query();
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'departments':
                $query = Department::with('institution'); // Carga la relación
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'workstations':
                $query = Workstation::with('department'); // Carga la relación
                if ($search) $query->where('name', 'like', "%{$search}%");
                break;
            case 'periods':
                $query = Period::query();
                if ($search) $query->where('name', 'like', "%{$search}%");
                $query->orderBy('is_active', 'desc')->orderBy('start_date', 'desc');
                break;
            case 'users':
                $query = User::with([
                    'institutions',
                    'roles'=> function($query) use ($activeInstitutionId){
                        $query->where('user_roles_institution.institution_id', $activeInstitutionId);
                    }
                ]); 
                if ($search) {
                    // !! BÚSQUEDA CORREGIDA !!
                    $query->where(function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                          ->orWhere('apellido_paterno', 'like', "%{$search}%")
                          ->orWhere('apellido_materno', 'like', "%{$search}%")
                          ->orWhere('RFC', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                }
                break;
        }
        return $query;
    }

    /**
     * Encuentra un item por ID para 'update' o 'destroy'.
     */
    private function findItem($seccion, $id)
    {
        switch ($seccion) {
            case 'institutions': return Institution::find($id);
            case 'departments': return Department::find($id);
            case 'workstations': return Workstation::find($id);
            case 'periods': return Period::find($id);
            case 'users': return User::find($id);
            default: return null;
        }
    }

    /**
     * Aplica filtros de autorización y de institución activa al query.
     */
    private function applyAuthFilters($query, $seccion)
    {
        $user = Auth::user();
        $activeInstitutionId = session('active_institution_id');

        // El Master de UMI ve UMI, el de MI ve MI.
        // Se asume que el Master solo debe ver datos de su institución activa.
        if ($seccion === 'users') {
            $query->whereHas('institutions', function($q) use ($activeInstitutionId) {
                $q->where('institutions.id', $activeInstitutionId);
            });
        } elseif ($seccion !== 'institutions') {
            $query->where('institution_id', $activeInstitutionId);
        }

        
        // Lógica para 'Control Académico' (asumiendo 'control_administrativo' como nombre de rol)
        if ($user->hasActiveRole('control_administrativo')) {
            // No puede ver 'institutions' ni 'workstations'
            if (in_array($seccion, ['institutions', 'workstations'])) {
                abort(403, 'No tienes permiso para ver esta sección.');
            }
            
            // Solo puede ver usuarios 'control_escolar' y 'docente'
            if ($seccion === 'users') {
                $query->whereHas('roles', function($q) {
                    $q->whereIn('name', ['control_escolar', 'docente']);
                });
            }
        }
    }

    /**
     * Obtiene los datos necesarios para los formularios (dropdowns, etc.)
     * y el item a editar (si se provee $id).
     */
    private function getFormData($seccion, $id = null)
    {
        $data = [];
        $activeInstitutionId = session('active_institution_id');
        $user = Auth::user();

        // 1. Encontrar el item si es para 'edit'
        if ($id) {
            $item = $this->findItem($seccion, $id);
            if (!$item) return ['item' => null]; // Dejar que el método principal maneje el 404
            
            // Aquí puedes añadir más validaciones (ej. que el item pertenezca a la $activeInstitutionId)
            
            $data['item'] = $item;
        }

        // 2. Obtener datos para dropdowns
        switch ($seccion) {
            case 'departments':
                // Solo mostrar la institución activa
                 $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                break;
            case 'workstations':
                // Solo departamentos de la institución activa
                $data['departments'] = Department::where('institution_id', $activeInstitutionId)->get();
                break;
            case 'periods':
                 // Solo para la institución activa
                 $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                break;
            case 'users':
                // Cargar roles según el permiso del usuario (basado en el DOCX)
                if ($user->hasActiveRole('master')) {
                    $data['roles'] = Role::whereIn('name', ['control_administrativo', 'gerente_th', 'gerente_capacitacion', 'anfitrion', 'master'])->get();
                } elseif ($user->hasActiveRole('control_administrativo')) {
                     $data['roles'] = Role::whereIn('name', ['control_escolar', 'docente'])->get();
                } else {
                    $data['roles'] = collect(); // Vacío
                }
                
                // Cargar datos de la institución activa
                $data['departments'] = Department::where('institution_id', $activeInstitutionId)->get();
                $data['workstations'] = Workstation::where('institution_id', $activeInstitutionId)->get();
                $data['institutions'] = Institution::where('id', $activeInstitutionId)->get();
                break;
        }

        return $data;
    }

    private function getSectionTitles($seccion)
{
    $titles = [
        'institutions' => [
            'plural' => 'Unidades de Negocio',
            'singular' => 'Unidad de Negocio'
        ],
        'departments' => [
            'plural' => 'Departamentos',
            'singular' => 'Departamento'
        ],
        'workstations' => [
            'plural' => 'Puestos',
            'singular' => 'Puesto'
        ],
        'periods' => [
            'plural' => 'Periodos',
            'singular' => 'Periodo'
        ],
        'users' => [
            'plural' => 'Usuarios',
            'singular' => 'Usuario'
        ],
    ];

    // Devuelve el título o un valor por defecto si no se encuentra
    return $titles[$seccion] ?? [
        'plural' => Str::title(str_replace('_', ' ', $seccion)),
        'singular' => Str::singular(Str::title(str_replace('_', ' ', $seccion)))
    ];
}
}