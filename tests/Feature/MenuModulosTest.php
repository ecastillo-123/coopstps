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
     * Módulos que se muestran en la barra superior con submenús desplegables.
     *
     * @var list<string>
     */
    private const MODULOS_SUPERIORES = [
        'administracion',
        'configuracion',
        'documentacion',
        'contractual',
        'colaboradores',
    ];

    /**
     * Módulos que se muestran en el menú lateral.
     *
     * @var list<string>
     */
    private const MODULOS_LATERALES = [
        'relaciones',
        'cumplimiento',
        'seguridad',
        'capacitacion',
        'mantenimiento',
        'auditorias',
        'simulacros',
    ];

    private function usuarioAdministrador(): User
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    /**
     * Devuelve el HTML comprendido entre dos marcadores de barra de navegación.
     */
    private function bloqueDeNavegacion(string $html, string $barra): string
    {
        $desde = 'data-nav="'.$barra.'"';

        $inicio = strpos($html, $desde);
        $this->assertNotFalse($inicio, "No se encontró la barra de navegación [{$barra}].");

        $resto = substr($html, $inicio + strlen($desde));

        $fin = strpos($resto, 'data-nav="');

        return $fin === false ? $resto : substr($resto, 0, $fin);
    }

    public function test_los_modulos_superiores_son_los_esperados(): void
    {
        $superiores = collect(config('sistema.modulos'))
            ->filter(fn (array $modulo): bool => ($modulo['ubicacion'] ?? 'lateral') === 'superior')
            ->keys()
            ->all();

        $this->assertSame(self::MODULOS_SUPERIORES, $superiores);
    }

    public function test_los_modulos_laterales_son_los_restantes(): void
    {
        $laterales = collect(config('sistema.modulos'))
            ->filter(fn (array $modulo): bool => ($modulo['ubicacion'] ?? 'lateral') !== 'superior')
            ->keys()
            ->all();

        $this->assertSame(self::MODULOS_LATERALES, $laterales);
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

    public function test_la_barra_superior_muestra_los_modulos_con_sus_submenus(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $superior = $this->bloqueDeNavegacion($html, 'superior');

        foreach (['Administrador', 'Configuración', 'Documentación', 'Contractual', 'Colaboradores y Trabajadores'] as $nombre) {
            $this->assertStringContainsString($nombre, $superior);
        }

        // Los submenús viajan con su módulo dentro de la barra superior.
        $this->assertStringContainsString('Usuarios y permisos', $superior);
        $this->assertStringContainsString('Registros patronales', $superior);
        $this->assertStringContainsString('Contratos individuales', $superior);
        $this->assertStringContainsString('Trabajadores', $superior);
    }

    public function test_la_barra_superior_no_incluye_los_modulos_laterales(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $superior = $this->bloqueDeNavegacion($html, 'superior');

        foreach (['Relaciones Laborales', 'Seguridad y Salud', 'Simulacros'] as $nombre) {
            $this->assertStringNotContainsString($nombre, $superior);
        }
    }

    public function test_el_menu_lateral_conserva_los_modulos_secundarios(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $lateral = $this->bloqueDeNavegacion($html, 'lateral');

        foreach (['Relaciones Laborales', 'Cumplimiento', 'Seguridad y Salud', 'Capacitación', 'Mantenimiento', 'Auditorías STPS', 'Simulacros'] as $nombre) {
            $this->assertStringContainsString($nombre, $lateral);
        }

        // Los módulos movidos a la barra superior ya no viven en el lateral.
        $this->assertStringNotContainsString('Usuarios y permisos', $lateral);
        $this->assertStringNotContainsString('Contratos individuales', $lateral);
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
