<?php

namespace Tests\Feature;

use App\Models\AnnualReview;
use App\Models\CentroTrabajo;
use App\Models\Hallazgo;
use App\Models\LegalEvidence;
use App\Models\NomRegla;
use App\Models\Puesto;
use App\Models\User;
use App\Support\Access\IndefiniteRetentionGate;
use App\Support\Access\LegalNomGate;
use App\Support\CentroTrabajoContext;
use App\Support\Sst\NomRuleEvaluator;
use Database\Seeders\CentroTrabajoSeeder;
use Database\Seeders\PuestoSeeder;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SstComplianceTest extends TestCase
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

    private function administrator(): User
    {
        $this->seedCatalogs();

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    private function sstUser(): User
    {
        $this->seedCatalogs();

        $puestoId = Puesto::where('clave', 'GER-RH')->value('id');
        $user = User::factory()->create([
            'puesto_id' => $puestoId,
        ]);
        $user->assignRole('Usuario');
        $user->centrosTrabajo()->attach(CentroTrabajo::factory()->create());

        return $user;
    }

    public function test_annual_review_can_be_recorded_by_administrator(): void
    {
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->post(route('annual-review.store'), [
                'year' => 2026,
                'outcome' => 'approved',
                'notes' => 'Todas las asignaciones fueron revisadas.',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('annual_reviews', [
            'year' => 2026,
            'outcome' => 'approved',
            'reviewed_by_user_id' => $admin->id,
        ]);
    }

    public function test_repeated_annual_review_updates_outcome(): void
    {
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->post(route('annual-review.store'), [
                'year' => 2026,
                'outcome' => 'approved',
                'notes' => 'Primera revisión.',
            ]);

        $this->actingAs($admin)
            ->post(route('annual-review.store'), [
                'year' => 2026,
                'outcome' => 'rejected',
                'notes' => 'Se encontraron inconsistencias.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('annual_reviews', [
            'year' => 2026,
            'outcome' => 'rejected',
        ]);
    }

    public function test_current_year_review_is_false_without_record(): void
    {
        $this->seedCatalogs();

        $this->assertFalse(AnnualReview::currentYearReviewed());
    }

    public function test_current_year_review_is_true_after_recording(): void
    {
        $admin = $this->administrator();

        $this->actingAs($admin)
            ->post(route('annual-review.store'), [
                'year' => now()->year,
                'outcome' => 'approved',
                'notes' => null,
            ]);

        $this->assertTrue(AnnualReview::currentYearReviewed());
    }

    public function test_legal_nom_automation_is_disabled_without_approved_evidence(): void
    {
        $this->seedCatalogs();

        $this->assertFalse(LegalNomGate::isActive());
    }

    public function test_legal_nom_automation_is_enabled_after_approved_dof_evidence(): void
    {
        $admin = $this->administrator();

        LegalEvidence::create([
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF 2026-01-15',
            'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=...',
            'verified_by_user_id' => $admin->id,
            'approved_by_user_id' => $admin->id,
            'approved' => true,
            'approved_at' => now(),
            'effective_at' => now()->subDay(),
        ]);

        $this->assertTrue(LegalNomGate::isActive());
    }

    public function test_unapproved_evidence_keeps_legal_nom_disabled(): void
    {
        $admin = $this->administrator();

        LegalEvidence::create([
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF 2026-01-15',
            'verified_by_user_id' => $admin->id,
            'approved' => false,
        ]);

        $this->assertFalse(LegalNomGate::isActive());
    }

    public function test_indefinite_retention_is_disabled_without_approved_evidence(): void
    {
        $this->seedCatalogs();

        $this->assertFalse(IndefiniteRetentionGate::isEnabled());
    }

    public function test_indefinite_retention_is_enabled_after_approved_evidence(): void
    {
        $admin = $this->administrator();

        LegalEvidence::create([
            'topic' => 'indefinite_retention',
            'reference' => 'DOF 2026-01-15 art. 123',
            'verified_by_user_id' => $admin->id,
            'approved_by_user_id' => $admin->id,
            'approved' => true,
            'approved_at' => now(),
            'effective_at' => now()->subDay(),
        ]);

        $this->assertTrue(IndefiniteRetentionGate::isEnabled());
    }

    public function test_unverified_or_unapproved_evidence_keeps_retention_disabled(): void
    {
        $admin = $this->administrator();

        LegalEvidence::create([
            'topic' => 'indefinite_retention',
            'reference' => 'DOF 2026-01-15 art. 123',
            'verified_by_user_id' => $admin->id,
            'approved' => false,
        ]);

        $this->assertFalse(IndefiniteRetentionGate::isEnabled());
    }

    public function test_page_12_image_remains_source_only_without_structured_rules(): void
    {
        $this->seedCatalogs();

        $this->assertFileExists(base_path('docs/1st_new.pdf'));

        $this->assertDatabaseMissing('nom_reglas', [
            'fuente' => 'page_12_image',
        ]);
    }

    public function test_sst_routes_require_authentication(): void
    {
        $this->get(route('sst.findings'))->assertRedirect('/login');
        $this->post(route('sst.findings.store'))->assertRedirect('/login');
        $this->get(route('sst.actions'))->assertRedirect('/login');
        $this->post(route('sst.actions.store'))->assertRedirect('/login');
    }

    public function test_authorized_user_can_record_sst_finding_with_risk_and_status(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('sst.findings.store'), [
                'titulo' => 'Falta de guardas en maquinaria',
                'descripcion' => 'Las guardas de la prensa #3 no están instaladas.',
                'riesgo' => 'alto',
                'estado' => 'abierto',
                'tipo' => 'seguridad',
                'referencia_nom' => 'NOM-004-STPS',
                'detected_at' => '2026-09-20',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('hallazgos', [
            'centro_trabajo_id' => $centro?->id,
            'titulo' => 'Falta de guardas en maquinaria',
            'riesgo' => 'alto',
            'estado' => 'abierto',
            'tipo' => 'seguridad',
        ]);
    }

    public function test_finding_with_different_risk_level_is_recorded(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('sst.findings.store'), [
                'titulo' => 'Iluminación insuficiente en área de trabajo',
                'descripcion' => 'Niveles de lux inferiores a los recomendados.',
                'riesgo' => 'medio',
                'estado' => 'en_progreso',
                'tipo' => 'salud',
                'detected_at' => '2026-09-21',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('hallazgos', [
            'centro_trabajo_id' => $centro?->id,
            'titulo' => 'Iluminación insuficiente en área de trabajo',
            'riesgo' => 'medio',
            'estado' => 'en_progreso',
            'tipo' => 'salud',
        ]);
    }

    public function test_sst_action_can_be_tracked_against_finding(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $hallazgo = Hallazgo::factory()->for($centro)->create();

        $this->actingAs($user)
            ->post(route('sst.actions.store'), [
                'hallazgo_id' => $hallazgo->id,
                'descripcion' => 'Instalar guardas de seguridad certificadas.',
                'responsable_id' => $user->id,
                'fecha_compromiso' => '2026-10-05',
                'estado' => 'pendiente',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('acciones_correctivas', [
            'hallazgo_id' => $hallazgo->id,
            'descripcion' => 'Instalar guardas de seguridad certificadas.',
            'estado' => 'pendiente',
        ]);
    }

    public function test_action_creation_is_denied_for_finding_outside_active_center(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        app(CentroTrabajoContext::class)->activo();

        $otherCenter = CentroTrabajo::factory()->create();
        $hallazgo = Hallazgo::factory()->for($otherCenter)->create();

        $this->actingAs($user)
            ->post(route('sst.actions.store'), [
                'hallazgo_id' => $hallazgo->id,
                'descripcion' => 'Acción sobre hallazgo externo.',
                'estado' => 'pendiente',
            ])
            ->assertNotFound();
    }

    public function test_legal_nom_rule_evaluation_is_fail_closed_without_approved_evidence(): void
    {
        $this->seedCatalogs();

        $rule = NomRegla::create([
            'codigo' => 'NOM-004-STPS-2019',
            'titulo' => 'Maquinaria y equipo',
            'descripcion' => 'Condiciones de seguridad para maquinaria y equipo.',
        ]);

        $this->assertFalse(NomRuleEvaluator::isActive($rule));
    }

    public function test_legal_nom_rule_evaluation_may_activate_with_approved_evidence(): void
    {
        $admin = $this->administrator();

        $rule = NomRegla::create([
            'codigo' => 'NOM-004-STPS-2019',
            'titulo' => 'Maquinaria y equipo',
            'descripcion' => 'Condiciones de seguridad para maquinaria y equipo.',
        ]);

        LegalEvidence::create([
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF 2026-01-15',
            'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=...',
            'verified_by_user_id' => $admin->id,
            'approved_by_user_id' => $admin->id,
            'approved' => true,
            'approved_at' => now(),
            'effective_at' => now()->subDay(),
        ]);

        $this->assertTrue(NomRuleEvaluator::isActive($rule));
    }

    public function test_authorized_user_can_record_sst_commission(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('sst.commissions.store'), [
                'nombre' => 'Comisión Interna de SST',
                'lider_id' => $user->id,
                'vigencia_inicio' => '2026-01-01',
                'vigencia_fin' => '2026-12-31',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('comisiones_sst', [
            'centro_trabajo_id' => $centro?->id,
            'nombre' => 'Comisión Interna de SST',
        ]);
    }

    public function test_authorized_user_can_record_sst_maintenance(): void
    {
        $user = $this->sstUser();
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('sst.maintenance.store'), [
                'equipo' => 'Extintor ABC - Área 1',
                'tipo' => 'preventivo',
                'frecuencia' => 'mensual',
                'ultima_fecha' => '2026-09-01',
                'proxima_fecha' => '2026-10-01',
                'responsable_id' => $user->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('mantenimiento_sst', [
            'centro_trabajo_id' => $centro?->id,
            'equipo' => 'Extintor ABC - Área 1',
        ]);
    }
}
