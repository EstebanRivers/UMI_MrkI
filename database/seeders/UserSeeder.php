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
// !!! VERIFICA ESTAS RUTAS !!! Asegúrate que coincidan con la ubicación REAL de tus modelos
use App\Models\Users\Department; // ¿Está aquí o en /Departments?
use App\Models\Users\Workstation; // ¿Está aquí o en /Workstations?
use App\Models\Users\Career; // ¿Está aquí o en /Careers?
// !!! FIN DE VERIFICACIÓN !!!
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        } else {
             $this->command->warn("Could not create Corporate Profile for Tribilin as Department/Workstation was missing.");
        }
        $this->command->info("Multi-role user 'Tribilin' created/updated and configured for UMI (Academic) and Palacio MI (Corporate).");


        // --- USUARIOS FICTICIOS PARA VOLUMEN ---

        // 7. Creamos 20 Estudiantes genéricos, con facturas de ESTUDIANTE aleatorias
        User::factory()->count(20)->has(Billing::factory()->count(rand(1, 5))->estudiante(), 'billings')
            ->create()->each(function ($user) use ($alumnoRole, $umi) { // Usar $alumnoRole
                if ($alumnoRole && $umi) {
                    $user->roles()->attach([$alumnoRole->id => ['institution_id' => $umi->id]]);
                } else { Log::warning("UserSeeder: Skip role assign estudiante"); }
            });

        $this->command->info('UserSeeder completado exitosamente.');
    }
}