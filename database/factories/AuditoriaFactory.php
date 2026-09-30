<?php

namespace Database\Factories;

use App\Models\Auditoria;
use App\Models\CentroTrabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auditoria>
 */
class AuditoriaFactory extends Factory
{
    protected $model = Auditoria::class;

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
            'tipo' => fake()->randomElement(['sst', 'legal', 'integral']),
            'fecha' => fake()->date(),
            'auditor_id' => null,
        ];
    }
}
