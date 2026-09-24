<?php

namespace Database\Factories;

use App\Models\Puesto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Puesto>
 */
class PuestoFactory extends Factory
{
    protected $model = Puesto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->unique()->jobTitle();

        return [
            'clave' => strtoupper(fake()->unique()->bothify('PUE-###')),
            'nombre' => $nombre,
            'activo' => true,
        ];
    }
}
