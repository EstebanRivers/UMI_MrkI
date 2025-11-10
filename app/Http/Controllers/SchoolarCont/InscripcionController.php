<?php

namespace App\Http\Controllers\SchoolarCont;

use App\Http\Controllers\Controller;
use App\Models\AdmonCont\Career;
use App\Models\Users\AcademicProfile;
use App\Models\Users\Address;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InscripcionController extends Controller
{
    //Formulario de Inscripción
    public function index(){
        // 1. Cargar las Carreras
        // Asume que el modelo se llama 'Carrera' y tiene las columnas 'id' y 'nombre'.
        $carreras = Career::all();

        // 2. Cargar otros datos para dropdowns (ejemplo de Campus)
        // $campuses = Campus::orderBy('nombre', 'asc')->get(); 

        // 3. Retornar la vista 'create' con los datos
        return view('layouts.ControlEsc.Inscripcion.index', compact('carreras' /*, 'campuses' */));
    }
    public function store(Request $request){
        // --- 1. VERIFICACIÓN / VALIDACIÓN DE DATOS ---
        $request->validate([
            // Reglas del Modelo User
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['nullable', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Reglas de la Dirección
            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            
            // Reglas Académicas
            'carrera' => ['required', 'integer', Rule::exists('carrers', 'id')],
        ]);
            // 1.2. PREPARACIÓN DE DATOS (RFC Genérico) ✅
            // ----------------------------------------------------
            $rfcFinal = $request->RFC;

            if (empty($rfcFinal)) {
                $rfcBase = 'XAXX010101000';
                $isUnique = false;
                $suffix = 0;
                
                do {
                    // Generar el RFC candidato: XAXX010101000, XAXX010101001, etc.
                    $rfcCandidato = $rfcBase . ($suffix > 0 ? $suffix : '');

                    // 🚨 Verificar en la base de datos si el RFC candidato ya existe
                    $isUnique = User::where('RFC', $rfcCandidato)->doesntExist();

                    if (!$isUnique) {
                        $suffix++;
                    }
                } while (!$isUnique && $suffix < 1000); // Límite el bucle para seguridad

                $rfcFinal = $rfcCandidato;
            }
            // ----------------------------------------------------

        DB::beginTransaction();

        try {
            // --- 1. GUARDAR LA DIRECCIÓN (MODELO ADDRESS) 📍 ---
            $address = Address::create([
                'calle' => $request->calle,
                'colonia' => $request->colonia,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ]);

            // --- 2. GUARDAR EL USUARIO (MODELO USER) 👤 ---
            $user = User::create([
                'nombre' => $request->nombre,
                'apellido_paterno' => $request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'RFC' => $rfcFinal,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'edad' => $request->edad,
                'address_id' => $address->id, // ¡Vinculado correctamente!
            ]);
            // --- 3. ASIGNAR EL ROL 'ALUMNO' (TABLA PIVOTE user_roles_institution) 🔑 ---
            
            // Claves que se insertarán en la tabla pivote: user_roles_institution
            $roleIdAlumno = 7;  // Estudiante
            $institutionId = 4; // UMI

            $user->roles()->attach($roleIdAlumno, [
                // Laravel inserta este valor en la columna 'institution_id'
                'institution_id' => $institutionId, 
            ]);
            // --- 4. GUARDAR EL PERFIL ACADÉMICO (MODELO ACADEMICPROFILE) 🎓 ---

            $academicProfile = AcademicProfile::create([
                'user_id' => $user->id, // La clave foránea del usuario recién creado
                'carrera_id' => $request->carrera, // El ID de la carrera seleccionado en el formulario

                // CAMPOS FALTANTES (Asumiendo valores predeterminados o nullables):
                'semestre' => 1,          // EJEMPLO: Siempre inicia en el semestre 1
                'status' => 'Aspirante',     // EJEMPLO: Estatus inicial
            ]);
            // --- 5. FINALIZACIÓN Y REDIRECCIÓN 🎉 ---

            // Si todos los pasos son exitosos, confirmamos los cambios
            DB::commit();
            return redirect()->route('Listas.students.index')->with('success', '¡El nuevo alumno ha sido registrado exitosamente!');
        } catch (\Exception $e) {
            // Si ocurre cualquier error, deshacemos todos los cambios
            DB::rollBack();


            // Redirigir de vuelta al formulario con un mensaje de error
            return back()->withInput()->with('error', 'Ocurrió un error en el registro. Inténtalo de nuevo.'. $e->getMessage());
            
        }

        

    }

    public function edit(string $id)
    {
        // 1. Buscar al usuario y cargar las relaciones necesarias
        // Usamos with(['address', 'academicProfile']) para cargar la información de dirección
        // y la información académica en una sola consulta, evitando problemas N+1.
        // findOrFail($id) asegura un error 404 si el ID no existe.
        $user = User::with(['address', 'academicProfile'])->findOrFail($id);

        // Opcional: Si quieres asegurar que solo se editen usuarios con el rol 'Alumno' (ID 7)
        // Descomenta la siguiente línea si es necesario
        
        if (!$user->roles()->where('role_id', 7)->exists()) {
            abort(403, 'Acceso no autorizado. Este usuario no es un alumno.');
        }
        
        
        // 2. Cargar la lista de carreras para llenar el dropdown
        $carreras = Career::all(); 

        // 3. Devolver la vista de edición con los datos
        return view('layouts.ControlAdmin.Listas.students.edit', compact('user', 'carreras'));
    }
    public function update(Request $request, string $id)
    {
        // --- 0. BUSCAR EL ALUMNO A EDITAR ---
        // Carga el alumno y sus relaciones para actualización
        $alumno = User::with(['address', 'academicProfile'])->findOrFail($id);

        // --- 1. VERIFICACIÓN / VALIDACIÓN DE DATOS ---
        $request->validate([
            // Reglas del Modelo User
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            // 🛑 CLAVE: El email debe ser único, EXCLUYENDO el ID del alumno actual.
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($alumno->id)],
            'telefono' => ['required', 'string', 'max:20'],
            'RFC' => ['nullable', 'string', 'max:13'],
            'fecha_nacimiento' => ['required', 'date'],
            // Contraseña es 'nullable': solo se requiere si se proporciona, y debe ser confirmada.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], 

            // Reglas de la Dirección (asumiendo que los campos son requeridos)
            'calle' => ['required', 'string', 'max:255'],
            'colonia' => ['required', 'string', 'max:255'],
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'digits:5'],
            
            // Reglas Académicas
            'carrera' => ['required', 'integer', Rule::exists('carrers', 'id')],
        ]);
        
        // 1.2. PREPARACIÓN DE DATOS (RFC Genérico) ✅
        // ----------------------------------------------------
        $rfcFinal = $request->RFC;
        
        // Si el usuario deja el campo vacío, asignamos el RFC genérico único
        if (empty($rfcFinal)) {
            $rfcBase = 'XAXX010101';
            $isUnique = false;
            $suffix = 0;
            
            do {
                // Generar el RFC candidato
                $suffixStr = str_pad($suffix, 3, '0', STR_PAD_LEFT);

                $rfcCandidato = $rfcBase . $suffixStr;
                // Verificar si el RFC candidato ya existe en OTROS usuarios
                // CLAVE: Ignoramos al usuario actual ($alumno->id) en la búsqueda de duplicados.
                $isUnique = User::where('RFC', $rfcCandidato)
                                ->where('id', '!=', $alumno->id) 
                                ->doesntExist();

                if (!$isUnique) {
                    $suffix++;
                }
            } while (!$isUnique && $suffix < 1000);

            $rfcFinal = $rfcCandidato;
        }
        // ----------------------------------------------------

        DB::beginTransaction();

        try {
            // --- 2. GESTIONAR LA DIRECCIÓN (CREAR O ACTUALIZAR) 📍 ---
            $addressData = [
                'calle' => $request->calle,
                'colonia' => $request->colonia,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'codigo_postal' => $request->codigo_postal,
            ];

            if ($alumno->address) {
                // La dirección ya existe, simplemente la actualizamos.
                $alumno->address->update($addressData);
                $addressId = $alumno->address_id;
            } else {
                // La dirección NO existe, la creamos y obtenemos su ID.
                $newAddress = Address::create($addressData);
                $addressId = $newAddress->id;
            }
            
            // Solo actualizamos la contraseña si el campo fue llenado
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $alumno->update($userData);
            
            // --- 4. ACTUALIZAR EL PERFIL ACADÉMICO (MODELO ACADEMICPROFILE) 🎓 ---
            // Usamos el objeto de relación cargado para actualizar el perfil existente
            $alumno->academicProfile->update([
                'carrera_id' => $request->carrera_id,
                
                // CAMPOS ADICIONALES QUE PUEDEN SER EDITADOS EN EL FUTURO:
                // 'semestre' => $request->semestre, // Si lo agregas al formulario
                // 'status' => $request->status,
            ]);
            
            // Nota: La asignación de Rol/Institución (user_roles_institution) NO necesita actualización aquí.

            // --- 5. FINALIZACIÓN Y REDIRECCIÓN 🎉 ---
            DB::commit();
            
            return redirect()->route('Listas.students.index')->with('success', '¡El alumno ha sido actualizado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Ocurrió un error en la actualización. Inténtalo de nuevo. ' . $e->getMessage());
        }
    }
}
