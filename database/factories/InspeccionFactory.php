<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\Inspeccion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inspeccion>
 */
class InspeccionFactory extends Factory
{
    protected $model = Inspeccion::class;

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
            'tipo' => fake()->randomElement(['seguridad', 'salud', 'maquinaria']),
            'fecha' => fake()->date(),
            'inspector_id' => null,
        ];
    }
}
