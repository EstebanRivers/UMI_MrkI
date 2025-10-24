<?php

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

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. Find Roles ---
        $masterRole = Role::where('name', 'master')->first();
        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();

        if (!$masterRole || !$alumnoRole || !$anfitrionRole) {
             $this->command->error('Essential roles (master, estudiante, anfitrion) not found. Run RoleSeeder first.');
             return;
        }

        // --- 2. Find Institutions ---
        $institutions = Institution::whereIn('name', [
            'Palacio Mundo Imperial',
            'Universidad Mundo Imperial',
            'Princess Mundo Imperial',
            'Pierre Mundo Imperial',
        ])->get();

        $umi = $institutions->firstWhere('name', 'Universidad Mundo Imperial');
        $palacioMI = $institutions->firstWhere('name', 'Palacio Mundo Imperial');

        if ($institutions->isEmpty() || !$umi || !$palacioMI) {
            $this->command->error('Essential institutions not found. Run InstitutionSeeder first.');
            return;
        }

        // --- 3. Create Supporting Data (Careers, Departments, Workstations) ---
        // Careers for UMI
        $ingenieriaCareer = Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $umi->id]);
        Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $umi->id]);

        // Departments for Palacio MI
        $talentoHumanoDep = Department::firstOrCreate(['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]);
        Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);

        // Workstations for Talento Humano at Palacio MI
        $reclutadorWorkstation = null;
        if ($talentoHumanoDep) {
             $reclutadorWorkstation = Workstation::firstOrCreate(
                 [
                     'name' => 'Reclutador',
                     'department_id' => $talentoHumanoDep->id,
                     'institution_id' => $talentoHumanoDep->institution_id, // Workstation belongs to Institution
                 ]
             );
             Workstation::firstOrCreate(
                 [
                     'name' => 'Analista de Nomina',
                     'department_id' => $talentoHumanoDep->id,
                     'institution_id' => $talentoHumanoDep->institution_id,
                 ]
             );
        } else {
            $this->command->error('Department "Talento Humano" not found for Palacio MI.');
            // Decide if you want to stop (return) or continue
        }

        // Check if workstation was created before proceeding
        if (!$ingenieriaCareer || !$reclutadorWorkstation) {
             $this->command->error('Error creating/finding necessary Career or Workstation.');
             return; // Stop seeding if essential data is missing
        }

        // --- 4. Create Master User ---
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Esteban',
                'apellido_paterno' => 'Rivera',
                'apellido_materno' => 'Molina',
                'password' => Hash::make('master1234'),
                'RFC' => 'XAXX010101000',
                // !! REMOVED department_id and workstation_id !!
            ]
        );

        // ✅ Link Master to ALL institutions (institution_user table)
        $institutionIds = $institutions->pluck('id')->toArray();
        $masterUser->institutions()->syncWithoutDetaching($institutionIds);
        $this->command->info("Master user linked to " . count($institutionIds) . " institutions in 'institution_user'.");

        // ✅ Assign 'master' role in EACH institution (user_roles_institution table)
        foreach ($institutions as $institution) {
            DB::table('user_roles_institution')->updateOrInsert(
                [
                    'user_id' => $masterUser->id,
                    'institution_id' => $institution->id,
                ],
                [
                    'role_id' => $masterRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info("Assigned 'master' role to Master user in each institution in 'user_roles_institution'.");

        // --- 5. Create Multi-Role User ('Tribilin') ---
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirol@UMI.com'],
            [
                'nombre' => 'Tribilin',
                'apellido_paterno' => 'Cobo',
                'apellido_materno' => 'Loquendo',
                'password' => Hash::make('contrasena'),
                'RFC' => 'GAPX010101XYZ',
                // !! REMOVED department_id and workstation_id !!
            ]
        );

        // --- Configure Tribilin for UMI (Academic) ---
        // ✅ Link to UMI (institution_user)
        $multiRoleUser->institutions()->syncWithoutDetaching([$umi->id]);

        // ✅ Assign 'estudiante' role at UMI (user_roles_institution)
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $umi->id],
            ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // ✅ Create Academic Profile (links user to career)
        AcademicProfile::updateOrCreate(
            ['user_id' => $multiRoleUser->id],
            ['career_id' => $ingenieriaCareer->id]
        );

        // --- Configure Tribilin for Palacio MI (Corporate) ---
        // ✅ Link to Palacio MI (institution_user)
        $multiRoleUser->institutions()->syncWithoutDetaching([$palacioMI->id]);

        // ✅ Assign 'anfitrion' role at Palacio MI (user_roles_institution)
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
            ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // ✅ Create Corporate Profile (links user to department/workstation)
        // Ensure $talentoHumanoDep and $reclutadorWorkstation are not null
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
    }
}