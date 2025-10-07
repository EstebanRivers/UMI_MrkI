<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Users\Institution;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Institution::firstOrCreate(['name' => 'Palacio Mundo Imperial']);
        Institution::firstOrCreate(['name' => 'Princess Mundo Imperial']);
        Institution::firstOrCreate(['name' => 'Pierre Mundo Imperial']);
        Institution::firstOrCreate(['name' => 'Universidad Mundo Imperial']);

    }
}