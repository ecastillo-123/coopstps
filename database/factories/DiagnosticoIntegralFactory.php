<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\DiagnosticoIntegral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiagnosticoIntegral>
 */
class DiagnosticoIntegralFactory extends Factory
{
    protected $model = DiagnosticoIntegral::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'titulo' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'area' => fake()->randomElement(['Seguridad y Salud', 'Legal', 'Ambiental']),
            'fecha' => fake()->date(),
            'responsable_id' => null,
        ];
    }
}
