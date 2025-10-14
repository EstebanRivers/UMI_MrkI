<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CarrerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Carrers::create([
            'official_id' => 'SEP/1234/5678',
            'name' => 'Licenciatura de Ejemplo',
        ]);
    }
}
