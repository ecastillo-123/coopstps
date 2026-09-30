<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\MantenimientoSst;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MantenimientoSst>
 */
class MantenimientoSstFactory extends Factory
{
    protected $model = MantenimientoSst::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'equipo' => fake()->words(3, true),
            'tipo' => fake()->randomElement(['preventivo', 'correctivo']),
            'frecuencia' => fake()->randomElement(['diaria', 'semanal', 'mensual', 'trimestral', 'anual']),
            'ultima_fecha' => fake()->date(),
            'proxima_fecha' => fake()->dateTimeBetween('+1 day', '+1 year')->format('Y-m-d'),
            'responsable_id' => User::factory(),
        ];
    }
}
