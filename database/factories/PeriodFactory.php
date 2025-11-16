<?php

namespace Database\Factories;

use App\Models\Periods\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        $year = $this->faker->numberBetween(2024, 2026);
        $isSemestreA = $this->faker->boolean;
        if ($isSemestreA) {
            $name = "Periodo $year-A (Feb-Jul)";
            $start_date = "$year-02-01"; $end_date = "$year-07-31"; $re_enrollment_date = "$year-01-15";
        } else {
            $name = "Periodo $year-B (Ago-Dic)";
            $start_date = "$year-08-01"; $end_date = "$year-12-31"; $re_enrollment_date = "$year-07-15";
        }
        return [
            'name' => $name,
            'start_date' => $start_date, 'end_date' => $end_date, 're_enrollment_date' => $re_enrollment_date,
            'is_active' => $this->faker->boolean(30),
        ];
    }
}