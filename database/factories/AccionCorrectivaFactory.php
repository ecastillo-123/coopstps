<?php

namespace Database\Factories;

use App\Models\AccionCorrectiva;
use App\Models\Hallazgo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccionCorrectiva>
 */
class AccionCorrectivaFactory extends Factory
{
    protected $model = AccionCorrectiva::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hallazgo_id' => Hallazgo::factory(),
            'descripcion' => fake()->paragraph(),
            'responsable_id' => User::factory(),
            'fecha_compromiso' => fake()->date(),
            'fecha_cierre' => fake()->optional()->date(),
            'estado' => fake()->randomElement(['pendiente', 'en_progreso', 'completada', 'cancelada']),
            'evidencia_url' => fake()->optional()->url(),
        ];
    }
}
