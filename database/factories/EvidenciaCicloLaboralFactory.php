<?php

namespace Database\Factories;

use App\Models\CambioCicloLaboral;
use App\Models\EvidenciaCicloLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvidenciaCicloLaboral>
 */
class EvidenciaCicloLaboralFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cambio_ciclo_laboral_id' => CambioCicloLaboral::factory(),
            'trabajador_id' => fn (array $attributes) => CambioCicloLaboral::query()
                ->findOrFail($attributes['cambio_ciclo_laboral_id'])
                ->trabajador_id,
            'centro_trabajo_id' => fn (array $attributes) => CambioCicloLaboral::query()
                ->findOrFail($attributes['cambio_ciclo_laboral_id'])
                ->centro_trabajo_id,
            'ruta' => fake()->unique()->uuid().'.pdf',
            'nombre_original' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'tamano_bytes' => 1024,
            'cargado_por' => null,
        ];
    }
}
