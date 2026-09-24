<?php

namespace Database\Seeders;

use App\Models\Puesto;
use Illuminate\Database\Seeder;

class PuestoSeeder extends Seeder
{
    /**
     * Catálogo inicial de puestos.
     */
    public function run(): void
    {
        $puestos = [
            ['clave' => 'ADMIN-SIS', 'nombre' => 'Administrador de Sistemas'],
            ['clave' => 'GER-RH', 'nombre' => 'Gerente de Recursos Humanos'],
            ['clave' => 'JEF-SST', 'nombre' => 'Jefe de Seguridad y Salud en el Trabajo'],
            ['clave' => 'ANL-RH', 'nombre' => 'Analista de Recursos Humanos'],
            ['clave' => 'AUD-STPS', 'nombre' => 'Auditor STPS'],
            ['clave' => 'COORD-OP', 'nombre' => 'Coordinador de Operaciones'],
        ];

        foreach ($puestos as $puesto) {
            Puesto::updateOrCreate(['clave' => $puesto['clave']], $puesto);
        }
    }
}
