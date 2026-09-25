<?php

namespace Database\Factories;

use App\Models\CategoriaRelacionLaboral;
use App\Models\ContratoTrabajador;
use App\Models\ModalidadContrato;
use App\Models\PlantillaContrato;
use App\Models\Trabajador;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContratoTrabajador> */
class ContratoTrabajadorFactory extends Factory
{
    protected $model = ContratoTrabajador::class;

    public function definition(): array
    {
        return [
            'trabajador_id' => Trabajador::factory(),
            'plantilla_contrato_id' => PlantillaContrato::factory(),
            'modalidad_contrato_id' => ModalidadContrato::factory(),
            'categoria_relacion_laboral_id' => CategoriaRelacionLaboral::factory(),
            'fecha_inicio' => fake()->date('Y-m-d', '-1 year'),
            'fecha_fin' => fake()->optional()->date('Y-m-d', '+1 year'),
            'salario' => fake()->randomFloat(2, 5000, 50000),
            'activo' => true,
        ];
    }
}
