<?php

namespace Database\Seeders;

use App\Models\CentroTrabajo;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Usuario administrador inicial del sistema.
     */
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@coopstps.test'],
            [
                'nombre' => 'Administrador',
                'apellido_paterno' => 'del Sistema',
                'apellido_materno' => null,
                'puesto_id' => Puesto::where('clave', 'ADMIN-SIS')->value('id'),
                'telefono' => '3300000001',
                'password' => 'Admin123!',
                'activo' => true,
            ],
        );

        $user->assignRole('Administrador');

        $centro = CentroTrabajo::where('clave', 'CT-0001')->first();

        if ($centro !== null) {
            $user->centrosTrabajo()->syncWithoutDetaching([$centro->id]);
        }
    }
}
