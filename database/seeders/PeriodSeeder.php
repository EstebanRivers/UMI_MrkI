<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periods\Period;
use App\Models\Users\Institution;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        // Intenta encontrar la institución
        $umi = Institution::where('name', 'Universidad Mundo Imperial')->first();

        if ($umi) {
            // 1. Crea 4 períodos aleatorios LIMPIOS
            // Usamos create() directamente en lugar de make()->each()
            // porque ya no necesitamos modificar el nombre manualmente.
            Period::factory()->count(4)->create([
                'institution_id' => $umi->id
            ]);
            
            // 2. Crea el periodo activo explícito (Periodo 2025-B)
            // Usamos firstOrCreate para evitar duplicados si corres el seeder dos veces
            Period::firstOrCreate(
                ['name' => 'Periodo 2025-B (Ago-Dic)'], // Busca por nombre
                [
                    'start_date' => '2025-08-01',
                    'end_date' => '2025-12-31',
                    're_enrollment_deadline' => '2025-07-15',
                    'is_active' => true,
                    'institution_id' => $umi->id
                ]
            );
            
        } else {
            $this->command->error('Institución "Universidad Mundo Imperial" no encontrada.');
        }
    }
}