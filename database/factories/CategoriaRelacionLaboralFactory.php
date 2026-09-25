<?php

namespace Database\Factories;

use App\Models\CategoriaRelacionLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CategoriaRelacionLaboral> */
class CategoriaRelacionLaboralFactory extends Factory
{
    protected $model = CategoriaRelacionLaboral::class;

    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->bothify('CAT-####'),
            'nombre' => fake()->words(2, true),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
