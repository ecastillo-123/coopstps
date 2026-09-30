<?php

namespace Database\Factories;

use App\Models\Capacitacion;
use App\Models\CentroTrabajo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Capacitacion>
 */
class CapacitacionFactory extends Factory
{
    protected $model = Capacitacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'nombre' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'tipo' => fake()->randomElement(['seguridad', 'salud', 'induccion', 'capacitacion']),
            'fecha_inicio' => fake()->date(),
            'fecha_fin' => fake()->date(),
            'instructor' => fake()->name(),
            'duracion_horas' => fake()->numberBetween(1, 40),
        ];
    }
}
