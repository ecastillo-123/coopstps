<?php

namespace Database\Factories;

use App\Models\NomRegla;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NomRegla>
 */
class NomReglaFactory extends Factory
{
    protected $model = NomRegla::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->bothify('NOM-###-STPS-####'),
            'titulo' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'fuente' => null,
        ];
    }
}
