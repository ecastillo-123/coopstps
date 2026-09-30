<?php

namespace Database\Factories;

use App\Models\CambioCicloLaboral;
use App\Models\CategoriaRelacionLaboral;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CambioCicloLaboral>
 */
class CambioCicloLaboralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trabajador_id' => Trabajador::factory(),
            'centro_trabajo_id' => fn (array $attributes) => Trabajador::query()
                ->findOrFail($attributes['trabajador_id'])
                ->centro_trabajo_id,
            'tipo' => fake()->randomElement(['ingreso', 'reingreso', 'baja']),
            'fecha_evento' => fake()->date(),
            'categoria_relacion_laboral_id' => CategoriaRelacionLaboral::factory(),
            'registrado_por' => null,
        ];
    }
}
