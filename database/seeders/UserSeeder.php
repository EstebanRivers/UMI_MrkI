<?php

namespace Database\Seeders;

use App\Models\Users\Institution;
use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Users\Department;
use App\Models\Users\Workstation;
use App\Models\Users\Career;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Añadido para manejo de errores

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // --- 1. Buscar Roles Necesarios ---
            $masterRole = Role::where('name', 'master')->firstOrFail();
            $alumnoRole = Role::where('name', 'estudiante')->firstOrFail();
            $anfitrionRole = Role::where('name', 'anfitrion')->firstOrFail();

            // --- 2. Buscar Instituciones Necesarias ---
            $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->firstOrFail();
            $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->firstOrFail();
            
            // Busca TODAS las instituciones a las que asignarás el master
            $allInstitutions = Institution::whereIn('name', [
                'Palacio Mundo Imperial',
                'Universidad Mundo Imperial',
                'Princess Mundo Imperial',
                'Pierre Mundo Imperial',
            ])->get();

            if ($allInstitutions->isEmpty()) {
                 throw new \Exception('Error Crítico: No se encontró ninguna institución para asignar al usuario Master.');
            }


            // --- 3. Crear Carreras, Departamentos y Puestos (Si no existen) ---
            Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $universidadMI->id]);
            Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $universidadMI->id]);
            
            $talentoHumanoDep = Department::firstOrCreate(
                ['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]
            );
            Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);

            Workstation::firstOrCreate(
                ['name' => 'Reclutador', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $palacioMI->id]
            );
            Workstation::firstOrCreate(
                ['name' => 'Analista de Nomina', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $palacioMI->id]
            );

            // --- 4. Crear Usuario Master ---
            $masterUser = User::firstOrCreate(
                ['email' => 'master@UMI.com'],
                [
                    'nombre' => 'Esteban',
                    'apellido_paterno' => 'Rivera',
                    'apellido_materno' => 'Molina',
                    'password' => Hash::make('master1234'),
                    'RFC' => 'XAXX010101000',
                    'role_id' => $masterRole->id, // Asigna rol principal
                    // !! CORRECCIÓN: Volvemos a añadir institution_id !!
                    // Asigna una institución primaria (ej. la primera encontrada)
                    'institution_id' => $allInstitutions->first()->id, 
                ]
            );

            // Asignar Master a TODAS sus instituciones (Ambas tablas pivote)
            foreach ($allInstitutions as $institution) {
                DB::table('user_roles_institution')->updateOrInsert(
                   ['user_id' => $masterUser->id, 'institution_id' => $institution->id],
                   ['role_id' => $masterRole->id, 'created_at' => now(), 'updated_at' => now()]
                );
                $masterUser->institutions()->syncWithoutDetaching($institution->id);
            }

            // --- 5. Crear Usuario Multi-Rol (Tribilin) ---
            $ingenieriaCareer = Career::where('name', 'Ingenieria en Sistemas')->where('institution_id', $universidadMI->id)->firstOrFail();
            $talentoHumanoDep = Department::where('name', 'Talento Humano')->where('institution_id', $palacioMI->id)->firstOrFail();
            $reclutadorWorkstation = Workstation::where('name', 'Reclutador')->where('department_id', $talentoHumanoDep->id)->firstOrFail();

            $multiRoleUser = User::firstOrCreate(
                ['email' => 'multirol@UMI.com'],
                [
                    'nombre' => 'Tribilin',
                    'apellido_paterno' => 'Cobo',
                    'apellido_materno' => 'Loquendo',
                    'password' => Hash::make('contrasena'),
                    'RFC' => 'GAPX010101XYZ',
                    'role_id' => $alumnoRole->id, // Rol principal default
                     
                    'institution_id' => $universidadMI->id,
                    'department_id' => $talentoHumanoDep->id, 
                    'workstation_id' => $reclutadorWorkstation->id 
                ]
            );

            // Asignar rol de "estudiante" en la Universidad
            DB::table('user_roles_institution')->updateOrInsert(
                ['user_id' => $multiRoleUser->id, 'institution_id' => $universidadMI->id],
                ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
            );
            $multiRoleUser->institutions()->syncWithoutDetaching($universidadMI->id);
            AcademicProfile::updateOrInsert(
                ['user_id' => $multiRoleUser->id],
                ['career_id' => $ingenieriaCareer->id]
            );

            // Asignar rol de "anfitrion" en el Palacio
            DB::table('user_roles_institution')->updateOrInsert(
                ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
                ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
            );
            $multiRoleUser->institutions()->syncWithoutDetaching($palacioMI->id);
            CorporateProfile::updateOrInsert(
                ['user_id' => $multiRoleUser->id],
                [
                    'department_id' => $talentoHumanoDep->id,
                    'workstation_id' => $reclutadorWorkstation->id,
                ]
            );
            
            $this->command->info('UserSeeder ejecutado exitosamente!');

        } catch (ModelNotFoundException $e) {
            $this->command->error("Error en UserSeeder: No se encontró un modelo necesario (Rol, Institución, Carrera, etc.). Verifica los nombres y que los Seeders anteriores se hayan ejecutado.");
            $this->command->error($e->getMessage());
        } catch (\Exception $e) {
            $this->command->error("Error inesperado en UserSeeder:");
            $this->command->error($e->getMessage());
             // Añade esto para más detalles si el error persiste
             \Log::error("Error en UserSeeder: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}