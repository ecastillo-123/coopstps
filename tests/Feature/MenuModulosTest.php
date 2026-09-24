<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuModulosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Orden esperado del menú lateral: primero los módulos de configuración y
     * operación, después los de evaluación y cumplimiento.
     *
     * @var list<string>
     */
    private const ORDEN_ESPERADO = [
        'administracion',
        'configuracion',
        'documentacion',
        'contractual',
        'colaboradores',
        'relaciones',
        'cumplimiento',
        'seguridad',
        'capacitacion',
        'mantenimiento',
        'auditorias',
        'simulacros',
    ];

    public function test_los_modulos_de_configuracion_y_operacion_van_al_inicio(): void
    {
        $this->assertSame(self::ORDEN_ESPERADO, array_keys(config('sistema.modulos')));
    }

    public function test_cada_modulo_tiene_al_menos_un_submenu(): void
    {
        foreach (config('sistema.modulos') as $clave => $modulo) {
            $this->assertNotEmpty(
                $modulo['items'],
                "El módulo [{$clave}] no tiene submenús definidos."
            );
        }
    }

    public function test_el_menu_renderiza_los_modulos_en_el_orden_definido(): void
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder([
                'Administrador',
                'Configuración',
                'Documentación',
                'Contractual',
                'Colaboradores y Trabajadores',
                'Relaciones Laborales',
                'Seguridad y Salud',
            ]);
    }

    public function test_un_usuario_sin_permisos_no_ve_modulos_administrativos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Usuarios y permisos')
            ->assertDontSee('Preferencias de avisos')
            ->assertDontSee('Registros patronales');
    }

    public function test_un_auditor_no_ve_modulos_de_gestion(): void
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Auditor');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Auditorías STPS')
            ->assertDontSee('Usuarios y permisos')
            ->assertDontSee('Contratos individuales');
    }
}
