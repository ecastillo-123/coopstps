<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MenuModulosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Los cuatro grupos superiores definidos por la fuente.
     *
     * @var list<string>
     */
    private const MODULOS_SUPERIORES = [
        'informacion_general',
        'administrador',
        'configuracion',
        'colaboradores',
    ];

    /**
     * Los dos bloques laterales definidos por la fuente.
     *
     * @var list<string>
     */
    private const MODULOS_LATERALES = [
        'relaciones_laborales',
        'seguridad_salud',
    ];

    /**
     * Rutas de workflows ya implementados que deben aparecer en la navegación.
     *
     * @var list<string>
     */
    private const RUTAS_IMPLEMENTADAS = [
        'admin.users.index',
        'admin.legal-evidence.index',
        'workforce.workers',
        'workforce.contracts',
        'sst.findings',
        'sst.actions',
        'sst.commissions',
        'sst.maintenance',
        'training.courses',
        'audit.inspections',
        'audit.audits',
        'audit.diagnosis',
    ];

    private function usuarioAdministrador(): User
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    /**
     * Devuelve el HTML comprendido dentro de una barra de navegación,
     * desde su marcador data-nav hasta el cierre de su etiqueta <nav>.
     */
    private function bloqueDeNavegacion(string $html, string $barra): string
    {
        $desde = 'data-nav="'.$barra.'"';

        $inicio = strpos($html, $desde);
        $this->assertNotFalse($inicio, "No se encontró la barra de navegación [{$barra}].");

        $resto = substr($html, $inicio + strlen($desde));

        $fin = strpos($resto, '</nav>');

        return $fin === false ? $resto : substr($resto, 0, $fin + strlen('</nav>'));
    }

    public function test_los_modulos_superiores_son_los_cuatro_grupos_de_la_fuente(): void
    {
        $superiores = collect(config('sistema.modulos'))
            ->filter(fn (array $modulo): bool => ($modulo['ubicacion'] ?? 'lateral') === 'superior')
            ->keys()
            ->all();

        $this->assertSame(self::MODULOS_SUPERIORES, $superiores);
    }

    public function test_los_modulos_laterales_son_los_dos_bloques_de_la_fuente(): void
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

    public function test_la_barra_superior_muestra_los_cuatro_grupos_con_submenus(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $superior = $this->bloqueDeNavegacion($html, 'superior');

        foreach (['Información General', 'Administrador', 'Configuración', 'Colaboradores / Trabajadores'] as $nombre) {
            $this->assertStringContainsString($nombre, $superior);
        }

        // Los workflows implementados del grupo superior deben estar disponibles.
        $this->assertStringContainsString('Trabajadores', $superior);
        $this->assertStringContainsString('Contratos', $superior);
    }

    public function test_la_barra_superior_no_incluye_los_bloques_laterales(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $superior = $this->bloqueDeNavegacion($html, 'superior');

        foreach (['Relaciones Laborales', 'Seguridad y Salud'] as $nombre) {
            $this->assertStringNotContainsString($nombre, $superior);
        }
    }

    public function test_el_menu_lateral_muestra_los_dos_bloques_de_la_fuente(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $lateral = $this->bloqueDeNavegacion($html, 'lateral');

        foreach (['Relaciones Laborales', 'Seguridad y Salud en el Trabajo'] as $nombre) {
            $this->assertStringContainsString($nombre, $lateral);
        }

        // Los workflows implementados del bloque de seguridad deben estar disponibles.
        foreach (['Hallazgos', 'Acciones correctivas', 'Capacitación', 'Auditorías', 'Diagnóstico integral'] as $nombre) {
            $this->assertStringContainsString($nombre, $lateral);
        }

        // Los grupos superiores no viven en el lateral.
        $this->assertStringNotContainsString('Colaboradores / Trabajadores', $lateral);
        $this->assertStringNotContainsString('Configuración', $lateral);
    }

    public function test_los_workflows_implementados_tienen_rutas_en_la_navegacion(): void
    {
        $rutas = collect(config('sistema.modulos'))
            ->pluck('items')
            ->flatten(1)
            ->filter(fn (array $item): bool => ($item['implementado'] ?? false) === true)
            ->pluck('ruta')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->assertSame(self::RUTAS_IMPLEMENTADAS, $rutas);

        foreach ($rutas as $ruta) {
            $this->assertTrue(Route::has($ruta), "La ruta [{$ruta}] no está registrada.");
        }
    }

    public function test_cada_modulo_superior_declara_un_color_distinto(): void
    {
        $colores = collect(config('sistema.modulos'))
            ->filter(fn (array $modulo): bool => ($modulo['ubicacion'] ?? 'lateral') === 'superior')
            ->pluck('color');

        $this->assertNotContains(null, $colores->all(), 'Todo módulo superior debe declarar un color.');

        $this->assertSame(
            $colores->count(),
            $colores->unique()->count(),
            'Los módulos superiores deben usar colores distintos entre sí.'
        );
    }

    public function test_los_botones_superiores_usan_el_color_configurado(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $clasesEsperadas = [
            'blue' => 'bg-blue-600',
            'indigo' => 'bg-indigo-600',
            'emerald' => 'bg-emerald-600',
            'amber' => 'bg-amber-500',
        ];

        foreach (config('sistema.modulos') as $modulo) {
            if (($modulo['ubicacion'] ?? 'lateral') !== 'superior') {
                continue;
            }

            $this->assertArrayHasKey(
                $modulo['color'],
                $clasesEsperadas,
                "El color [{$modulo['color']}] no existe en la paleta del layout."
            );

            $this->assertStringContainsString($clasesEsperadas[$modulo['color']], $html);
        }
    }

    public function test_los_bloques_laterales_tienen_colores_distintos(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $lateral = $this->bloqueDeNavegacion($html, 'lateral');

        // Relaciones Laborales se representa con tonos ámbar (carpeta amarilla).
        $this->assertStringContainsString('amber', $lateral);

        // Seguridad y Salud se representa con tonos azules.
        $this->assertStringContainsString('sky', $lateral);
    }

    public function test_un_usuario_sin_permisos_no_ve_modulos_administrativos(): void
    {
        $user = User::factory()->create();

        $html = $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $superior = $this->bloqueDeNavegacion($html, 'superior');
        $lateral = $this->bloqueDeNavegacion($html, 'lateral');

        foreach (['Usuarios y permisos', 'Registros patronales', 'Trabajadores'] as $nombre) {
            $this->assertStringNotContainsString($nombre, $superior);
            $this->assertStringNotContainsString($nombre, $lateral);
        }
    }

    public function test_un_auditor_ve_solo_los_modulos_que_le_corresponden(): void
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Auditor');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Seguridad y Salud en el Trabajo')
            ->assertDontSee('Usuarios y permisos')
            ->assertDontSee('Configuración')
            ->assertDontSee('Colaboradores / Trabajadores');
    }

    public function test_unsupported_page_24_route_returns_404(): void
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Usuario');

        $this->actingAs($user)
            ->get('/page-24-undefined-module')
            ->assertNotFound();
    }
}
