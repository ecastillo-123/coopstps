<?php

namespace Tests\Feature;

use App\Models\CentroTrabajo;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentroTrabajoActivoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_contexto_toma_el_primer_centro_asignado(): void
    {
        $user = User::factory()->create();

        $centro = CentroTrabajo::factory()->create(['nombre' => 'Planta Norte']);
        $user->centrosTrabajo()->attach($centro);

        $this->actingAs($user);

        $this->assertSame($centro->id, app(CentroTrabajoContext::class)->activo()?->id);
    }

    public function test_el_usuario_puede_cambiar_su_centro_activo(): void
    {
        $user = User::factory()->create();

        $primero = CentroTrabajo::factory()->create(['nombre' => 'A Corporativo']);
        $segundo = CentroTrabajo::factory()->create(['nombre' => 'B Planta']);

        $user->centrosTrabajo()->attach([$primero->id, $segundo->id]);

        $this->actingAs($user)
            ->post('/centro-trabajo-activo', ['centro_trabajo_id' => $segundo->id])
            ->assertRedirect();

        $this->assertSame($segundo->id, session('centro_trabajo_activo_id'));
    }

    public function test_no_puede_activar_un_centro_que_no_le_pertenece(): void
    {
        $user = User::factory()->create();
        $ajeno = CentroTrabajo::factory()->create();

        $this->actingAs($user)
            ->post('/centro-trabajo-activo', ['centro_trabajo_id' => $ajeno->id])
            ->assertNotFound();
    }

    public function test_el_dashboard_muestra_los_modulos_del_mapa_stps(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Módulo central de cumplimiento')
            ->assertSee('Seguridad y Salud')
            ->assertSee('Simulacros');
    }
}
