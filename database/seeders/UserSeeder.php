<?php

namespace Database\Seeders;

// Mantén todas tus declaraciones 'use' existentes
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
        // 1. Busca roles
        $masterRole = Role::where('name', 'master')->first();
        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();

        if (!$masterRole || !$alumnoRole || !$anfitrionRole) {
             $this->command->error('Roles esenciales (master, estudiante, anfitrion) no encontrados. Ejecuta RoleSeeder primero.');
             return;
        }

        // 2. Busca instituciones
        $institutions = Institution::whereIn('name', [
            'Palacio Mundo Imperial',
            'Universidad Mundo Imperial',
            'Princess Mundo Imperial',
            'Pierre Mundo Imperial',
        ])->get();

        $umi = $institutions->firstWhere('name', 'Universidad Mundo Imperial');
        $palacioMI = $institutions->firstWhere('name', 'Palacio Mundo Imperial');

        if ($institutions->isEmpty() || !$umi || !$palacioMI) {
            $this->command->error('Instituciones esenciales no encontradas. Ejecuta InstitutionSeeder primero.');
            return;
        }

        // --- USUARIO MASTER ---
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Esteban',
                'apellido_paterno' => 'Rivera',
                'apellido_materno' => 'Molina',
                'password' => Hash::make('master1234'),
                'RFC' => 'XAXX010101000',
                // 'role_id' => $masterRole->id, // !! ELIMINADO !!
                'department_id' => null, // Mantén estos si existen en tu tabla users
                'workstation_id' => null, // Mantén estos si existen en tu tabla users
                // 'institution_id' => $umi->id // !! ELIMINADO !!
            ]
        );

        // ✅ Vincular Master a TODAS las instituciones (Tabla: institution_user)
        // Usamos Eloquent para más limpieza
        $institutionIds = $institutions->pluck('id')->toArray();
        $masterUser->institutions()->syncWithoutDetaching($institutionIds);
        $this->command->info("Usuario Master vinculado a " . count($institutionIds) . " instituciones en 'institution_user'.");

        // ✅ Asignar rol Master en CADA institución (Tabla: user_roles_institution)
        foreach ($institutions as $institution) {
            DB::table('user_roles_institution')->updateOrInsert(
                [
                    'user_id' => $masterUser->id,
                    'institution_id' => $institution->id,
                ],
                [
                    'role_id' => $masterRole->id, // Asignar el rol 'master' aquí
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        $this->command->info("Rol 'master' asignado al usuario Master en cada institución en 'user_roles_institution'.");


        // --- CARRERAS, DEPARTAMENTOS, PUESTOS (Tu lógica aquí parece correcta) ---
        $ingenieriaCareer = Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $umi->id]);
        Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $umi->id]);

        $talentoHumanoDep = Department::firstOrCreate(['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]);
        Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);

        $reclutadorWorkstation = null;
        if ($talentoHumanoDep) {
             $reclutadorWorkstation = Workstation::firstOrCreate(
                 [
                     'name' => 'Reclutador',
                     'department_id' => $talentoHumanoDep->id,
                     'institution_id' => $talentoHumanoDep->institution_id,
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
            $this->command->error('Departamento "Talento Humano" no encontrado para Palacio MI.');
            return; // O maneja el error como prefieras
        }

        if (!$ingenieriaCareer || !$reclutadorWorkstation) {
             $this->command->error('Error creando/encontrando Carrera o Puesto necesarios.');
             return; // O maneja el error
        }

        // --- USUARIO MULTIROL ('Tribilin') ---
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirol@UMI.com'],
            [
                'nombre' => 'Tribilin',
                'apellido_paterno' => 'Cobo',
                'apellido_materno' => 'Loquendo',
                'password' => Hash::make('contrasena'),
                'RFC' => 'GAPX010101XYZ',
                // 'institution_id' => $umi->id, // !! ELIMINADO !!
                // 'role_id' => $alumnoRole->id // !! ELIMINADO !!
                // Puedes mantener department_id y workstation_id si representan su puesto "principal" o "por defecto"
                'department_id' => $talentoHumanoDep->id,
                'workstation_id' => $reclutadorWorkstation->id,
            ]
        );

        // ✅ Vincular Tribilin a UMI (Tabla: institution_user)
        $multiRoleUser->institutions()->syncWithoutDetaching([$umi->id]);

        // ✅ Asignar rol de "estudiante" en la Universidad (Tabla: user_roles_institution)
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $umi->id],
            ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Crear perfil académico
        AcademicProfile::updateOrCreate( // Cambiado a updateOrCreate por si el seeder se corre múltiples veces
            ['user_id' => $multiRoleUser->id],
            ['career_id' => $ingenieriaCareer->id]
        );

        // ✅ Vincular Tribilin al Palacio (Tabla: institution_user)
        $multiRoleUser->institutions()->syncWithoutDetaching([$palacioMI->id]);

        // ✅ Asignar rol de "anfitrion" en el Palacio (Tabla: user_roles_institution)
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
            ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Crear perfil corporativo
        CorporateProfile::updateOrCreate( // Cambiado a updateOrCreate
            ['user_id' => $multiRoleUser->id],
            [
                'department_id' => $talentoHumanoDep->id,
                'workstation_id' => $reclutadorWorkstation->id,
            ]
        );

        $this->command->info("Usuario Multirol 'Tribilin' creado/actualizado y configurado para UMI y Palacio MI.");
    }
}