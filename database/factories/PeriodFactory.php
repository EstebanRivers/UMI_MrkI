<?php

namespace Database\Factories;

use App\Models\Periods\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        // Genera un año aleatorio para evitar colisiones
        $year = $this->faker->unique()->numberBetween(2023, 2027);
        $isSemestreA = $this->faker->boolean;
        
        if ($isSemestreA) {
            $name = "Periodo $year-A (Feb-Jul)";
            $start_date = "$year-02-01"; 
            $end_date = "$year-07-31"; 
            $re_enrollment_deadline = "$year-01-15";
        } else {
            $name = "Periodo $year-B (Ago-Dic)";
            $start_date = "$year-08-01"; 
            $end_date = "$year-12-31"; 
            $re_enrollment_deadline = "$year-07-15";
        }
        
        return [
            // El nombre debe ser único
            'name' => $name,
            'start_date' => $start_date, 
            'end_date' => $end_date, 
            
            // Nombre de columna corregido
            're_enrollment_deadline' => $re_enrollment_deadline,
            
            'is_active' => $this->faker->boolean(30),
        ];
    }
}