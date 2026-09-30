<?php

namespace Tests\Feature;

use App\Models\CentroTrabajo;
use App\Models\DiagnosticoIntegral;
use App\Models\Puesto;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Database\Seeders\CentroTrabajoSeeder;
use Database\Seeders\PuestoSeeder;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingAuditReportingTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalogs(): void
    {
        $this->seed([
            SistemaSeeder::class,
            PuestoSeeder::class,
            CentroTrabajoSeeder::class,
        ]);
    }

    private function trainingUser(): User
    {
        $this->seedCatalogs();

        $user = User::factory()->create([
            'puesto_id' => Puesto::where('clave', 'GER-RH')->value('id'),
        ]);
        $user->assignRole('Usuario');
        $user->centrosTrabajo()->attach(CentroTrabajo::factory()->create());

        return $user;
    }

    private function auditorUser(?string $puestoClave = null): User
    {
        $this->seedCatalogs();

        $user = User::factory()->create([
            'puesto_id' => $puestoClave !== null
                ? Puesto::where('clave', $puestoClave)->value('id')
                : null,
        ]);
        $user->assignRole('Auditor');
        $user->centrosTrabajo()->attach(CentroTrabajo::factory()->create());

        return $user;
    }

    public function test_guest_is_redirected_to_login_for_training_audit_reporting_routes(): void
    {
        $this->get(route('training.courses'))->assertRedirect('/login');
        $this->post(route('training.courses.store'))->assertRedirect('/login');
        $this->get(route('audit.inspections'))->assertRedirect('/login');
        $this->post(route('audit.inspections.store'))->assertRedirect('/login');
        $this->get(route('audit.audits'))->assertRedirect('/login');
        $this->post(route('audit.audits.store'))->assertRedirect('/login');
        $this->get(route('audit.diagnosis'))->assertRedirect('/login');
        $this->post(route('audit.diagnosis.store'))->assertRedirect('/login');
        $this->post(route('reports.export'))->assertRedirect('/login');
    }

    public function test_auditor_can_write_inspection(): void
    {
        $user = $this->auditorUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('audit.inspections.store'), [
                'titulo' => 'Inspección de guardas',
                'tipo' => 'seguridad',
                'fecha' => '2026-09-25',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('inspecciones', [
            'centro_trabajo_id' => $centro?->id,
            'titulo' => 'Inspección de guardas',
        ]);
    }

    public function test_auditor_can_write_audit(): void
    {
        $user = $this->auditorUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('audit.audits.store'), [
                'titulo' => 'Auditoría interna de SST',
                'tipo' => 'sst',
                'fecha' => '2026-09-25',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('auditorias', [
            'centro_trabajo_id' => $centro?->id,
            'titulo' => 'Auditoría interna de SST',
        ]);
    }

    public function test_auditor_cannot_write_training(): void
    {
        $user = $this->auditorUser();

        $this->actingAs($user)
            ->post(route('training.courses.store'), [
                'nombre' => 'Curso de alturas',
            ])
            ->assertForbidden();
    }

    public function test_auditor_cannot_export_reports(): void
    {
        $user = $this->auditorUser();

        $this->actingAs($user)
            ->post(route('reports.export'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_export_reports(): void
    {
        $user = $this->trainingUser();

        $this->actingAs($user)
            ->post(route('reports.export'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_training_write_requires_active_center(): void
    {
        $this->seedCatalogs();

        $user = User::factory()->create([
            'puesto_id' => Puesto::where('clave', 'GER-RH')->value('id'),
        ]);
        $user->assignRole('Usuario');

        $this->actingAs($user)
            ->post(route('training.courses.store'), [
                'nombre' => 'Curso de alturas',
            ])
            ->assertForbidden();
    }

    public function test_audit_write_requires_active_center(): void
    {
        $this->seedCatalogs();

        $user = User::factory()->create();
        $user->assignRole('Auditor');

        $this->actingAs($user)
            ->post(route('audit.inspections.store'), [
                'titulo' => 'Inspección sin centro',
            ])
            ->assertForbidden();
    }

    public function test_authorized_user_can_record_training_course(): void
    {
        $user = $this->trainingUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('training.courses.store'), [
                'nombre' => 'Curso de trabajo en alturas',
                'descripcion' => 'Uso de arnés y líneas de vida.',
                'tipo' => 'seguridad',
                'fecha_inicio' => '2026-10-01',
                'fecha_fin' => '2026-10-03',
                'instructor' => 'Ing. Seguridad',
                'duracion_horas' => 16,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('capacitaciones', [
            'centro_trabajo_id' => $centro?->id,
            'nombre' => 'Curso de trabajo en alturas',
        ]);
    }

    public function test_non_auditor_cannot_write_inspection(): void
    {
        $user = $this->trainingUser();

        $this->actingAs($user)
            ->post(route('audit.inspections.store'), [
                'titulo' => 'Inspección no autorizada',
            ])
            ->assertForbidden();
    }

    public function test_auditor_with_eligible_position_cannot_create_integral_diagnosis(): void
    {
        $user = $this->auditorUser('GER-RH');

        $this->actingAs($user)
            ->post(route('audit.diagnosis.store'), [
                'titulo' => 'Diagnóstico integral 2026',
                'area' => 'Seguridad y Salud',
                'fecha' => '2026-09-25',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('diagnosticos_integrales', [
            'titulo' => 'Diagnóstico integral 2026',
        ]);
    }

    public function test_auditor_cannot_see_integral_diagnosis_write_form(): void
    {
        $user = $this->auditorUser();

        $this->actingAs($user)
            ->get(route('audit.diagnosis'))
            ->assertDontSee('Guardar diagnóstico');
    }

    public function test_auditor_cannot_update_integral_diagnosis_through_an_unregistered_method(): void
    {
        $user = $this->auditorUser('GER-RH');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $diagnostico = DiagnosticoIntegral::factory()->for($centro)->create([
            'titulo' => 'Diagnóstico vigente',
        ]);

        $this->put(route('audit.diagnosis.store'), [
            'titulo' => 'Diagnóstico modificado',
        ])->assertMethodNotAllowed();

        $this->assertDatabaseHas('diagnosticos_integrales', [
            'id' => $diagnostico->id,
            'titulo' => 'Diagnóstico vigente',
        ]);
    }

    public function test_auditor_cannot_delete_integral_diagnosis_through_an_unregistered_method(): void
    {
        $user = $this->auditorUser('GER-RH');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $diagnostico = DiagnosticoIntegral::factory()->for($centro)->create([
            'titulo' => 'Diagnóstico vigente',
        ]);

        $this->delete(route('audit.diagnosis.store'))->assertMethodNotAllowed();

        $this->assertModelExists($diagnostico);
    }

    public function test_unsupported_page_24_route_returns_404(): void
    {
        $user = $this->trainingUser();

        $this->actingAs($user)
            ->get('/page-24-undefined-module')
            ->assertNotFound();
    }
}
