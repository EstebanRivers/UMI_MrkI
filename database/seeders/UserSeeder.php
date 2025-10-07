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
        $palacioMI = Institution::where('name', 'Palacio Mundo Imperial')->first();
        $universidadMI = Institution::where('name', 'Universidad Mundo Imperial')->first();
        $princessMI = Institution::where('name', 'Princess Mundo Imperial')->first();
        $pierreMI = Institution::where('name', 'Pierre Mundo Imperial')->first();

        // 2. Encontrar los roles
        $masterRole = Role::where('name', 'master')->first();

        // 3. Crear el Usuario Master
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
        $masterUser->institutions()->sync([
            $palacioMI->id,
            $universidadMI->id,
            $princessMI->id,
            $pierreMI->id,
        ]);
        
        $masterUser->roles()->sync([
            $masterRole->id,
        ]);
    }
}