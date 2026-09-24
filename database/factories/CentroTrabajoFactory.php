<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CentroTrabajo>
 */
class CentroTrabajoFactory extends Factory
{
    protected $model = CentroTrabajo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clave' => strtoupper(fake()->unique()->bothify('CT-####')),
            'nombre' => fake()->company(),
            'numero_registro_patronal' => strtoupper(fake()->bothify('##########')),
            'rfc' => strtoupper(fake()->bothify('????######???')),
            'estado' => fake()->state(),
            'ciudad' => fake()->city(),
            'telefono' => fake()->numerify('##########'),
            'email' => fake()->unique()->companyEmail(),
            'activo' => true,
        ];
    }
}
