<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periods\Period;
use App\Models\Users\Institution; // Asegúrate que la ruta sea correcta

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $umi = Institution::where('name', 'Universidad Mundo Imperial')->first();

        if ($umi) {
            // Crea 4 períodos de prueba aleatorios
            Period::factory()->count(4)->create([
                'institution_id' => $umi->id
            ]);
            
             // Crea un periodo activo para 2025-B
            Period::factory()->create([
                'name' => 'Periodo 2025-B (Ago-Dic)',
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-31',
                're_enrollment_date' => '2025-07-15',
                'is_active' => true,
                'institution_id' => $umi->id
            ]);
        } else {
            $this->command->error('Institución "Universidad Mundo Imperial" no encontrada. No se crearon períodos.');
        }
    }
}