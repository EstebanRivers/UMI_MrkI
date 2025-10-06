<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\User;
use App\Models\Users\Role;
use App\Models\Users\Institution;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Encontrar las instituciones (ya creadas por InstitutionSeeder)
        $forumInstitution = Institution::where('name', 'Forum')->first();
        $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->first();

        // 2. Encontrar los roles
        $adminRole = Role::where('name', 'master')->first();

        // 3. Crear el Usuario Master
        $masterUser = User::firstOrCreate(
            ['email' => 'master@UMI.com'],
            [
                'nombre' => 'Usuario',
                'apellido_paterno' => 'Master',
                'apellido_materno' => '',
                'password' => Hash::make('master1234'),
                'RFC' => 'XAXX010101000',
            ]
        );

        // 4. Asignar MÚLTIPLES instituciones y roles al usuario
        $masterUser->institution()->sync([
            $forumInstitution->id,
            $universidadMI->id,
        ]);
        
        $masterUser->roles()->sync([
            $adminRole->id,
        ]);
    }
}