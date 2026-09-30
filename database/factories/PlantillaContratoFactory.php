<?php

namespace Database\Factories;

use App\Models\PlantillaContrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlantillaContrato> */
class PlantillaContratoFactory extends Factory
{
    protected $model = PlantillaContrato::class;

    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->bothify('PLT-####'),
            'nombre' => fake()->sentence(3),
            'contenido' => fake()->paragraph(),
            'activo' => true,
        ];
    }
}
