<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\AcademicProfile;
use App\Models\Users\CorporateProfile;
use App\Models\Users\Institution;
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
        // 1. Busca el rol que quieres asignar
        $masterRole = Role::where('name', 'master')->first(); // O el rol que necesites

        // 2. Busca TODAS las instituciones a las que quieres asignarlo
        $institutions = Institution::whereIn('name', [
            'Palacio Mundo Imperial',
            'Universidad Mundo Imperial',
            'Princess Mundo Imperial',
            'Pierre Mundo Imperial',
        ])->get();

        // 3. Crea el usuario master
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Esteban',
                'apellido_paterno' => 'Rivera',
                'apellido_materno' => 'Molina',
                'password' => Hash::make('master1234'),
                'RFC' => 'XAXX010101000',
            ]
        );

        // 4. Asignar MÚLTIPLES instituciones y roles al usuario
        foreach ($institutions as $institution) {
        // Inserta una nueva fila en la tabla 'role_user_institution'
        // que vincula al usuario, el rol y la institución actual del bucle.
        DB::table('user_roles_institution')->insert([
            'user_id' => $masterUser->id,
            'role_id' => $masterRole->id,
            'institution_id' => $institution->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        }

        $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->first();
            if ($universidadMI) {
                Career::firstOrCreate(['name' => 'Ingenieria en Sistemas', 'institution_id' => $universidadMI->id]);
                Career::firstOrCreate(['name' => 'Administracion de Empresas', 'institution_id' => $universidadMI->id]);
            }

        $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->first();
            if ($palacioMI) {
                Department::firstOrCreate(['name' => 'Talento Humano', 'institution_id' => $palacioMI->id]);
                Department::firstOrCreate(['name' => 'Calidad', 'institution_id' => $palacioMI->id]);
            }
        
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
        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();

        if (!$alumnoRole || !$anfitrionRole || !$palacioMI || !$universidadMI) {
            // Detiene el seeder y muestra un error claro.
            throw new \Exception('No se pudo encontrar un Rol o Institución. Revisa los nombres en UserSeeder.');
        }

        // 2. Obtener los perfiles específicos
        $ingenieriaCareer = Career::where('name', 'Ingenieria en Sistemas')->where('institution_id', $universidadMI->id)->first();
        $talentoHumanoDep = Department::where('name', 'Talento Humano')->where('institution_id', $palacioMI->id)->first();
        $reclutadorWorkstation = Workstation::where('name', 'Reclutador')->where('department_id', $talentoHumanoDep->id)->first();

        // Asegurarse de que los perfiles específicos existen
        if (!$ingenieriaCareer || !$talentoHumanoDep || !$reclutadorWorkstation) {
            throw new \Exception('Error en el Seeder: No se encontró la carrera, departamento o puesto especificado.');
        }

        // 3. Crear el usuario "Tribilin"
        $multiRoleUser = User::firstOrCreate(
            ['email' => 'multirol@UMI.com'],
            [
                'nombre' => 'Tribilin',
                'apellido_paterno' => 'Cobo',
                'apellido_materno' => 'Loquendo',
                'password' => Hash::make('contrasena'),
                'RFC' => 'GAPX010101XYZ',
            ]
        );

       // 4. Asignar rol de "estudiante" y perfil académico en la Universidad
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $universidadMI->id],
            ['role_id' => $alumnoRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // CORRECCIÓN AQUÍ: Busca el perfil solo por user_id
        AcademicProfile::updateOrInsert(
            ['user_id' => $multiRoleUser->id],
            ['career_id' => $ingenieriaCareer->id]
        );


        // 5. Asignar rol de "anfitrion" y perfil corporativo en el Palacio
        DB::table('user_roles_institution')->updateOrInsert(
            ['user_id' => $multiRoleUser->id, 'institution_id' => $palacioMI->id],
            ['role_id' => $anfitrionRole->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // CORRECCIÓN AQUÍ: Busca el perfil solo por user_id
        CorporateProfile::updateOrInsert(
            ['user_id' => $multiRoleUser->id],
            [
                'department_id' => $talentoHumanoDep->id,
                'workstation_id' => $reclutadorWorkstation->id,
            ]
        );
    }
}