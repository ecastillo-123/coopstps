<?php

namespace Database\Factories;

use App\Models\CentroTrabajo;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Trabajador> */
class TrabajadorFactory extends Factory
{
    protected $model = Trabajador::class;

    public function definition(): array
    {
        return [
            'centro_trabajo_id' => CentroTrabajo::factory(),
            'numero_empleado' => fake()->unique()->bothify('EMP-####'),
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'curp' => strtoupper(fake()->bothify('????######????###')),
            'nss' => fake()->numerify('###########'),
            'rfc' => strtoupper(fake()->bothify('????######???')),
            'fecha_nacimiento' => fake()->date('Y-m-d', '-18 years'),
            'fecha_ingreso' => fake()->date('Y-m-d', '-1 year'),
            'activo' => true,
        ];
    }
}
