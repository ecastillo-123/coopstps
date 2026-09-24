<?php

namespace Database\Seeders;

use App\Models\CentroTrabajo;
use Illuminate\Database\Seeder;

class CentroTrabajoSeeder extends Seeder
{
    /**
     * Centro de trabajo de demostración para el selector de contexto.
     */
    public function run(): void
    {
        CentroTrabajo::updateOrCreate(
            ['clave' => 'CT-0001'],
            [
                'nombre' => 'Corporativo Central',
                'numero_registro_patronal' => 'B0000000010',
                'rfc' => 'COO000101ABC',
                'estado' => 'Jalisco',
                'ciudad' => 'Guadalajara',
                'telefono' => '3300000000',
                'email' => 'corporativo@coopstps.test',
                'activo' => true,
            ],
        );
    }
}
