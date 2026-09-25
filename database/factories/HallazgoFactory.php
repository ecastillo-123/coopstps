<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\Hallazgo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hallazgo>
 */
class HallazgoFactory extends Factory
{
    protected $model = Hallazgo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'trabajador_id' => null,
            'titulo' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'riesgo' => fake()->randomElement(['bajo', 'medio', 'alto', 'critico']),
            'estado' => fake()->randomElement(['abierto', 'en_progreso', 'cerrado']),
            'tipo' => fake()->randomElement(['seguridad', 'salud', 'nom']),
            'referencia_nom' => fake()->optional()->bothify('NOM-###-STPS-####'),
            'detected_at' => fake()->date(),
        ];
    }
}
