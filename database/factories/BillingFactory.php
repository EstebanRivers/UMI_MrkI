<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Facturacion\Billing;
use App\Models\Periods\Period;

class BillingFactory extends Factory
{
    protected $model = Billing::class;

    public function definition(): array
    {
        static $sequence = 1;
        $uid = "FAC-" . str_pad($sequence++, 3, "0", STR_PAD_LEFT);
        $periodId = Period::inRandomOrder()->first()->id ?? null;
        return [
            'factura_uid' => $uid,
            'concepto' => 'Pago de servicio genérico',
            'monto' => $this->faker->randomFloat(2, 500, 10000),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('+1 week', '+1 year'),
            'status' => $this->faker->randomElement(['Pendiente', 'Pagada']),
            'archivo_path' => 'facturas/placeholder.pdf',
            'period_id' => $periodId,
        ];
    }

    /**
     * Define un estado para generar conceptos de facturas para estudiantes.
     */
    public function estudiante()
    {
        return $this->state(function (array $attributes) {
            $conceptos = [
                'Pago de Inscripción Licenciatura',
                'Mensualidad Bachillerato - ' . fake()->monthName(),
                'Pago de Reinscripción Maestría',
                'Curso de Idiomas - Inglés',
                'Trámite de Titulación',
                'Examen Extraordinario de Matemáticas',
                'Colegiatura Licenciatura en Gastronomía',
                'Pago de material de laboratorio',
            ];
            return [
                'concepto' => fake()->randomElement($conceptos),
            ];
        });
    }

    /**
     * Define un estado para generar conceptos de facturas para docentes.
     */
    public function docente()
    {
        return $this->state(function (array $attributes) {
            $conceptos = [
                'Honorarios Docente - ' . fake()->monthName(),
                'Pago por curso de verano',
                'Bono de productividad',
                'Viáticos para conferencia',
                'Reembolso de material didáctico',
            ];
            return [
                'concepto' => fake()->randomElement($conceptos),
            ];
        });
    }
}
