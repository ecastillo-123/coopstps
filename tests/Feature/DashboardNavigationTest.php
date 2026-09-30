<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioAdministrador(): User
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    public function test_el_dashboard_muestra_los_cuatro_ejes_central_de_cumplimiento(): void
    {
        $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Indicadores')
            ->assertSee('Alertas')
            ->assertSee('Normas y obligaciones')
            ->assertSee('Simulacros');
    }

    public function test_el_dashboard_muestra_los_workflows_implementados(): void
    {
        $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Trabajadores')
            ->assertSee('Contratos')
            ->assertSee('Hallazgos')
            ->assertSee('Acciones correctivas')
            ->assertSee('Comisiones SST')
            ->assertSee('Mantenimiento')
            ->assertSee('Capacitación')
            ->assertSee('Inspecciones')
            ->assertSee('Auditorías')
            ->assertSee('Diagnóstico integral');
    }

    public function test_el_dashboard_no_muestra_modulos_de_la_pagina_24_no_implementados(): void
    {
        $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Asistente de identificación de NOMs')
            ->assertDontSee('Sustancias químicas')
            ->assertDontSee('Simulacros de incendio');
    }

    public function test_el_dashboard_muestra_el_centro_de_trabajo_activo(): void
    {
        $user = $this->usuarioAdministrador();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Centro de trabajo activo');
    }

    public function test_los_links_de_workflows_apuntan_a_rutas_conocidas(): void
    {
        $html = $this->actingAs($this->usuarioAdministrador())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $rutas = [
            route('workforce.workers'),
            route('workforce.contracts'),
            route('sst.findings'),
            route('sst.actions'),
            route('sst.commissions'),
            route('sst.maintenance'),
            route('training.courses'),
            route('audit.inspections'),
            route('audit.audits'),
            route('audit.diagnosis'),
        ];

        foreach ($rutas as $ruta) {
            $this->assertStringContainsString($ruta, $html);
        }
    }
}
