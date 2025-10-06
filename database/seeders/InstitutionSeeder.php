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
        Institution::firstOrCreate(['name' => 'Forum']);
        Institution::firstOrCreate(['name' => 'Universidad Mundo Imperial']);
    }
}