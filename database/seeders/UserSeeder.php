<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\Institution;
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
        $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->first();

        $alumnoRole = Role::where('name', 'estudiante')->first();
        $anfitrionRole = Role::where('name','anfitrion')->first();

        if (!$alumnoRole || !$anfitrionRole || !$palacioMI || !$universidadMI) {
            // Detiene el seeder y muestra un error claro.
            throw new \Exception('No se pudo encontrar un Rol o Institución. Revisa los nombres en UserSeeder.');
        }

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

        // Asignar rol "estudiante" SOLO en la Universidad
        DB::table('user_roles_institution')->insert([
            'user_id' => $multiRoleUser->id,
            'role_id' => $alumnoRole->id,
            'institution_id' => $universidadMI->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asignar rol "anfitrion" SOLO en el Palacio
        DB::table('user_roles_institution')->insert([
            'user_id' => $multiRoleUser->id,
            'role_id' => $anfitrionRole->id,
            'institution_id' => $palacioMI->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}