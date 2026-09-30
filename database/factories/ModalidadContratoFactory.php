<?php

namespace Database\Factories;

use App\Models\ModalidadContrato;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ModalidadContrato> */
class ModalidadContratoFactory extends Factory
{
    protected $model = ModalidadContrato::class;

    public function definition(): array
    {
        return [
            'clave' => fake()->unique()->bothify('MOD-####'),
            'nombre' => fake()->words(2, true),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }
}
