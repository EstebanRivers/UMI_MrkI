<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

// Keep all your existing 'use' statements
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
<<<<<<< Updated upstream
=======
use Illuminate\Database\Eloquent\ModelNotFoundException; 
>>>>>>> Stashed changes
use App\Models\Facturacion\Billing;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. Find Roles & Verify ---
        $masterRole = Role::where('name', 'master')->first();
        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();
        $docenteRole = Role::where('name', 'docente')->first();

        if (!$masterRole || !$alumnoRole || !$anfitrionRole || !$docenteRole) {
             $missing = collect(['master', 'estudiante', 'anfitrion', 'docente'])
                ->filter(fn($name) => !Role::where('name', $name)->exists())
                ->implode(', ');
            Log::error("UserSeeder Error: Faltan roles esenciales ({$missing}). Ejecuta RoleSeeder primero.");
            $this->command->error("UserSeeder Error: Faltan roles esenciales ({$missing}). Ejecuta RoleSeeder primero.");
             return;
        }

        // --- 2. Find Institutions & Verify ---
        $institutions = Institution::whereIn('name', [
            'Palacio Mundo Imperial', 'Universidad Mundo Imperial', 'Princess Mundo Imperial', 'Pierre Mundo Imperial',
        ])->get();
        $umi = $institutions->firstWhere('name', 'Universidad Mundo Imperial');
        $palacioMI = $institutions->firstWhere('name', 'Palacio Mundo Imperial');

        if ($institutions->isEmpty() || !$umi || !$palacioMI) {
            Log::error("UserSeeder Error: Faltan instituciones esenciales. Ejecuta InstitutionSeeder primero.");
            $this->command->error('UserSeeder Error: Faltan instituciones esenciales. Ejecuta InstitutionSeeder primero.');
            return;
        }

        // --- 3. Create Supporting Data (Careers, Departments, Workstations) & Verify ---
        $ingenieriaCareer = Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $umi->id]);
        Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $umi->id]);

        $talentoHumanoDep = Department::firstOrCreate(['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]);
        Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);

        $reclutadorWorkstation = null;
        if ($talentoHumanoDep) {
             $reclutadorWorkstation = Workstation::firstOrCreate([
                 'name' => 'Reclutador', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $talentoHumanoDep->institution_id,
             ]);
             Workstation::firstOrCreate([
                 'name' => 'Analista de Nomina', 'department_id' => $talentoHumanoDep->id, 'institution_id' => $talentoHumanoDep->institution_id,
             ]);
        } else {
            Log::error('UserSeeder Error: Department "Talento Humano" not found for Palacio MI.');
            $this->command->error('UserSeeder Error: Department "Talento Humano" not found for Palacio MI.');
        }

        // Verifica si los datos de soporte se crearon antes de continuar
        if (!$ingenieriaCareer) {
             Log::error('UserSeeder Error: Error creating/finding necessary Career.');
             $this->command->error('UserSeeder Error: Error creating/finding necessary Career.');
             return;
        }
         if (!$reclutadorWorkstation && $talentoHumanoDep) { // Only critical if TH Dep exists
            Log::error('UserSeeder Error: Error creating/finding necessary Workstation.');
             $this->command->error('UserSeeder Error: Error creating/finding necessary Workstation.');
             return;
        }


        // --- 4. Create Master User ---
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Esteban',
                'apellido_paterno' => 'Rivera',
                'apellido_materno' => 'Molina',
                'password' => Hash::make('master1234'), // Contraseña específica
                'RFC' => 'XAXX010101000',
            ]
        );
        $institutionIds = $institutions->pluck('id')->toArray();
        $masterUser->institutions()->syncWithoutDetaching($institutionIds);
        $this->command->info("Master user linked to " . count($institutionIds) . " institutions in 'institution_user'.");
        foreach ($institutions as $institution) {
            DB::table('user_roles_institution')->updateOrInsert(
                ['user_id' => $masterUser->id, 'institution_id' => $institution->id],
                ['role_id' => $masterRole->id, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        $this->command->info("Assigned 'master' role to Master user in each institution.");


        // --- 5. Create Multi-Role User ('Tribilin') ---
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirol@UMI.com'],
            [
                'nombre' => 'Tribilin',
                'apellido_paterno' => 'Cobo',
                'apellido_materno' => 'Loquendo',
                'password' => Hash::make('contrasena'), // Contraseña específica
                'RFC' => 'GAPX010101XYZ',
            ]
        );
        // Configure Tribilin for UMI (Academic - estudiante)
        $multiRoleUser->institutions()->syncWithoutDetaching([$umi->id]);
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $umi->id],
            ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
        );
        AcademicProfile::updateOrCreate(
            ['user_id' => $multiRoleUser->id],
            ['career_id' => $ingenieriaCareer->id]
        );
        // Configure Tribilin for Palacio MI (Corporate - anfitrion)
        $multiRoleUser->institutions()->syncWithoutDetaching([$palacioMI->id]);
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
            ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
        );
        if ($talentoHumanoDep && $reclutadorWorkstation) {
            CorporateProfile::updateOrCreate(
                ['user_id' => $multiRoleUser->id],
                [
                    'department_id' => $talentoHumanoDep->id,
                    'workstation_id' => $reclutadorWorkstation->id,
                ]
            );
<<<<<<< Updated upstream
        } else {
             $this->command->warn("Could not create Corporate Profile for Tribilin as Department/Workstation was missing.");
=======
            
            $this->command->info('UserSeeder ejecutado exitosamente!');



        } catch (ModelNotFoundException $e) {
            $this->command->error("Error en UserSeeder: No se encontró un modelo necesario (Rol, Institución, Carrera, etc.). Verifica los nombres y que los Seeders anteriores se hayan ejecutado.");
            $this->command->error($e->getMessage());
        } catch (\Exception $e) {
            $this->command->error("Error inesperado en UserSeeder:");
            $this->command->error($e->getMessage());
             // Añade esto para más detalles si el error persiste
             \Log::error("Error en UserSeeder: " . $e->getMessage() . "\n" . $e->getTraceAsString());
>>>>>>> Stashed changes
        }
        $this->command->info("Multi-role user 'Tribilin' created/updated and configured for UMI (Academic) and Palacio MI (Corporate).");
        $this->command->info('Generando 20 estudiantes aleatorios...');

            User::factory()
                ->count(20)
                // Crea facturas aleatorias para probar el módulo de facturación
                ->has(Billing::factory()->count(rand(1, 3))->estudiante(), 'billings')
                ->create([
                    // IMPORTANTE: Forzamos el ID de UMI para evitar el error "Field institution_id doesn't have a default value"
                    'institution_id' => $universidadMI->id 
                ])
                ->each(function ($user) use ($alumnoRole, $universidadMI) {
                    
                    // 1. Asignar Rol en tabla pivote
                    $user->roles()->attach($alumnoRole->id, ['institution_id' => $universidadMI->id]);
                    
                    // 2. Sincronizar Institución
                    $user->institutions()->syncWithoutDetaching($universidadMI->id);
                    
                    // 3. Crear Perfil Académico (Asigna una carrera al azar para que no de error al ver detalles)
                    $carreraRandom = Career::where('institution_id', $universidadMI->id)->inRandomOrder()->first();
                    
                    if($carreraRandom) {
                        AcademicProfile::create([
                            'user_id' => $user->id,
                            'career_id' => $carreraRandom->id,
                        ]);
                    }
                });

            $this->command->info('20 Estudiantes creados exitosamente.');


        $this->command->info('UserSeeder completado exitosamente.');
    }
}