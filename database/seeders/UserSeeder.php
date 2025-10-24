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

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Busca el rol master
        $masterRole = Role::where('name', 'master')->first();

        // 2. Busca TODAS las instituciones
        $institutions = Institution::whereIn('name', [
            'Palacio Mundo Imperial',
            'Universidad Mundo Imperial',
            'Princess Mundo Imperial',
            'Pierre Mundo Imperial',
        ])->get();

        // 3. Busca la institución UMI
        $umi = Institution::where('name', 'Universidad Mundo Imperial')->first();
        if (!$umi) {
            $umi = Institution::first();
        }

        // 4. Crea el usuario master
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Esteban',
                'apellido_paterno' => 'Rivera',
                'apellido_materno' => 'Molina',
                'password' => Hash::make('master1234'),
                'RFC' => 'XAXX010101000',
                'institution_id' => $umi->id
            ]
        );

        // ✅ CRÍTICO: Vincular el usuario a TODAS las instituciones en la tabla pivote
        foreach ($institutions as $institution) {
            // PRIMERO: Sincronizar en institution_user (sin duplicados)
            DB::table('institution_user')->updateOrInsert(
                [
                    'user_id' => $masterUser->id,
                    'institution_id' => $institution->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // SEGUNDO: Insertar en user_roles_institution
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

        // Crear carreras
        $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->first();
        if ($universidadMI) {
            Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $universidadMI->id]);
            Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $universidadMI->id]);
        }

        // Crear departamentos
        $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->first();
        if ($palacioMI) {
            Department::firstOrCreate(['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]);
            Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);
        }

        // Crear puestos
        $talentoHumanoDep = Department::where('name', 'Talento Humano')->first();
        if ($talentoHumanoDep) {
            Workstation::firstOrCreate(
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
        }

        // Obtener roles
        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();

        if (!$alumnoRole || !$anfitrionRole || !$palacioMI || !$universidadMI) {
            throw new \Exception('No se pudo encontrar un Rol o Institución. Revisa los nombres en UserSeeder.');
        }

        // Obtener perfiles específicos
        $ingenieriaCareer = Career::where('name', 'Ingenieria en Sistemas')->where('institution_id', $universidadMI->id)->first();
        $talentoHumanoDep = Department::where('name', 'Talento Humano')->where('institution_id', $palacioMI->id)->first();
        $reclutadorWorkstation = Workstation::where('name', 'Reclutador')->where('department_id', $talentoHumanoDep->id)->first();

        if (!$ingenieriaCareer || !$talentoHumanoDep || !$reclutadorWorkstation) {
            throw new \Exception('Error en el Seeder: No se encontró la carrera, departamento o puesto especificado.');
        }

        // Crear el usuario "Tribilin"
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirol@UMI.com'],
            [
                'nombre' => 'Tribilin',
                'apellido_paterno' => 'Cobo',
                'apellido_materno' => 'Loquendo',
                'password' => Hash::make('contrasena'),
                'RFC' => 'GAPX010101XYZ',
                'institution_id' => $umi->id
            ]
        );

        // ✅ CRÍTICO: Vincular a la Universidad en institution_user
        DB::table('institution_user')->updateOrInsert(
            [
                'user_id' => $multiRoleUser->id,
                'institution_id' => $universidadMI->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Asignar rol de "estudiante" en la Universidad
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $universidadMI->id],
            ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Crear perfil académico
        AcademicProfile::updateOrInsert(
            ['user_id' => $multiRoleUser->id],
            ['career_id' => $ingenieriaCareer->id]
        );

        // ✅ CRÍTICO: Vincular al Palacio en institution_user
        DB::table('institution_user')->updateOrInsert(
            [
                'user_id' => $multiRoleUser->id,
                'institution_id' => $palacioMI->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Asignar rol de "anfitrion" en el Palacio
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
            ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Crear perfil corporativo
        CorporateProfile::updateOrInsert(
            ['user_id' => $multiRoleUser->id],
            [
                'department_id' => $talentoHumanoDep->id,
                'workstation_id' => $reclutadorWorkstation->id,
            ]
        );
    }
}