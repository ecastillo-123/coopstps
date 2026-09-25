<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\ComisionSst;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComisionSst>
 */
class ComisionSstFactory extends Factory
{
    protected $model = ComisionSst::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'nombre' => fake()->company().' - Comisión SST',
            'lider_id' => User::factory(),
            'vigencia_inicio' => fake()->date(),
            'vigencia_fin' => fake()->dateTimeBetween('+6 months', '+1 year')->format('Y-m-d'),
        ];
    }
}
