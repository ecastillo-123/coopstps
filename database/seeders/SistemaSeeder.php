<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SistemaSeeder extends Seeder
{
    /**
     * Sincroniza el catálogo de permisos y roles definido en config/sistema.php.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = collect(config('sistema.modulos'))
            ->pluck('permiso')
            ->filter()
            ->unique()
            ->values();

        $permisos->each(fn (string $permiso) => Permission::findOrCreate($permiso, 'web'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (config('sistema.roles') as $nombre => $definicion) {
            $rol = Role::findOrCreate($nombre, 'web');

            $rol->syncPermissions(
                in_array('*', $definicion['permisos'], true)
                    ? $permisos->all()
                    : $definicion['permisos']
            );
        }
    }
}
