<?php

namespace Tests\Feature;

use App\Models\LegalEvidence;
use App\Models\User;
use App\Support\Access\IndefiniteRetentionGate;
use App\Support\Access\LegalNomGate;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LegalEvidenceAdministrationTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $this->seed(SistemaSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('Administrador');

        return $user;
    }

    private function approvedEvidence(User $administrator, string $topic): LegalEvidence
    {
        return LegalEvidence::factory()->create([
            'topic' => $topic,
            'reference' => 'DOF 2026-01-15, artículo 123',
            'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
            'verified_by_user_id' => $administrator->id,
            'approved_by_user_id' => $administrator->id,
            'approved' => true,
            'approved_at' => now(),
            'effective_at' => today(),
        ]);
    }

    public function test_administrator_can_discover_and_render_legal_evidence_management(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertSee(route('admin.legal-evidence.index'), false);

        $this->actingAs($administrator)
            ->get(route('admin.legal-evidence.index'))
            ->assertSee('Fundamento legal')
            ->assertSee('Registrar y aprobar evidencia')
            ->assertSee('Activación de reglas NOM')
            ->assertSee('Retención indefinida')
            ->assertSee(route('admin.legal-evidence.store'), false);
    }

    public function test_administrator_can_register_and_approve_official_dof_evidence(): void
    {
        $this->travelTo('2026-09-29 12:00:00');
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->from(route('admin.legal-evidence.index'))
            ->post(route('admin.legal-evidence.store'), [
                'topic' => 'legal_nom_activation',
                'reference' => 'DOF 2026-01-15, artículo 123',
                'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
                'effective_at' => '2026-01-15',
            ])
            ->assertRedirect(route('admin.legal-evidence.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('legal_evidences', [
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF 2026-01-15, artículo 123',
            'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
            'verified_by_user_id' => $administrator->id,
            'approved_by_user_id' => $administrator->id,
            'approved' => true,
            'approved_at' => '2026-09-29 12:00:00',
        ]);

        $this->assertSame('2026-01-15', LegalEvidence::query()->sole()->effective_at->toDateString());
        $this->assertTrue(LegalNomGate::isActive());
    }

    public function test_administrator_can_register_and_approve_indefinite_retention_evidence(): void
    {
        $this->travelTo('2026-09-29 12:00:00');
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->post(route('admin.legal-evidence.store'), [
                'topic' => 'indefinite_retention',
                'reference' => 'DOF 2026-01-15, artículo 123',
                'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
                'effective_at' => '2026-01-15',
            ])
            ->assertRedirect(route('admin.legal-evidence.index'));

        $this->assertTrue(IndefiniteRetentionGate::isEnabled());
    }

    public static function invalidEvidenceInputs(): array
    {
        return [
            'spoofed DOF host' => [
                ['evidence_url' => 'https://dof.gob.mx.example.org/legal'],
                'La URL debe pertenecer al DOF oficial, usar HTTPS e incluir una ruta.',
            ],
            'insecure DOF URL' => [
                ['evidence_url' => 'http://dof.gob.mx/legal'],
                'La URL debe pertenecer al DOF oficial, usar HTTPS e incluir una ruta.',
            ],
            'blank legal reference' => [
                ['reference' => '   '],
                'La referencia legal es obligatoria.',
            ],
            'malformed effective date' => [
                ['effective_at' => 'not-a-date'],
                'La fecha de vigencia debe tener el formato AAAA-MM-DD.',
            ],
        ];
    }

    #[DataProvider('invalidEvidenceInputs')]
    public function test_administrator_cannot_approve_evidence_with_invalid_details(
        array $overrides,
        string $message,
    ): void {
        $administrator = $this->administrator();
        $payload = array_merge([
            'topic' => 'legal_nom_activation',
            'reference' => 'DOF 2026-01-15, artículo 123',
            'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
            'effective_at' => '2026-01-15',
        ], $overrides);

        $this->actingAs($administrator)
            ->from(route('admin.legal-evidence.index'))
            ->followingRedirects()
            ->post(route('admin.legal-evidence.store'), $payload)
            ->assertSee($message);

        $this->assertDatabaseCount('legal_evidences', 0);
    }

    public function test_administrator_can_revoke_approval_and_both_legal_gates_close(): void
    {
        $this->travelTo('2026-09-29 12:00:00');
        $administrator = $this->administrator();
        $legalNomEvidence = $this->approvedEvidence($administrator, 'legal_nom_activation');
        $retentionEvidence = $this->approvedEvidence($administrator, 'indefinite_retention');

        $this->assertTrue(LegalNomGate::isActive());
        $this->assertTrue(IndefiniteRetentionGate::isEnabled());

        $this->actingAs($administrator)
            ->from(route('admin.legal-evidence.index'))
            ->post(route('admin.legal-evidence.revoke', $legalNomEvidence), [
                'reason' => 'La publicación oficial fue sustituida.',
            ])
            ->assertRedirect(route('admin.legal-evidence.index'))
            ->assertSessionHas('status');

        $this->assertFalse(LegalNomGate::isActive());
        $this->assertTrue(IndefiniteRetentionGate::isEnabled());

        $this->actingAs($administrator)
            ->from(route('admin.legal-evidence.index'))
            ->post(route('admin.legal-evidence.revoke', $retentionEvidence), [
                'reason' => 'La publicación oficial fue sustituida.',
            ])
            ->assertRedirect(route('admin.legal-evidence.index'))
            ->assertSessionHas('status');

        foreach ([$legalNomEvidence, $retentionEvidence] as $evidence) {
            $this->assertDatabaseHas('legal_evidences', [
                'id' => $evidence->id,
                'approved' => false,
                'revoked_by_user_id' => $administrator->id,
                'revoked_at' => '2026-09-29 12:00:00',
                'revocation_reason' => 'La publicación oficial fue sustituida.',
            ]);
        }

        $this->assertFalse(LegalNomGate::isActive());
        $this->assertFalse(IndefiniteRetentionGate::isEnabled());
    }

    public function test_revocation_requires_a_reason_and_keeps_approval_active_on_validation_failure(): void
    {
        $administrator = $this->administrator();
        $evidence = $this->approvedEvidence($administrator, 'legal_nom_activation');

        $this->actingAs($administrator)
            ->from(route('admin.legal-evidence.index'))
            ->post(route('admin.legal-evidence.revoke', $evidence), ['reason' => '   '])
            ->assertRedirect(route('admin.legal-evidence.index'))
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseHas('legal_evidences', [
            'id' => $evidence->id,
            'approved' => true,
            'revoked_at' => null,
        ]);
    }

    public function test_non_administrator_cannot_open_or_submit_legal_evidence_actions(): void
    {
        $administrator = $this->administrator();
        $evidence = $this->approvedEvidence($administrator, 'legal_nom_activation');
        $user = User::factory()->create();
        $user->assignRole('Usuario');

        $this->actingAs($user)
            ->get(route('admin.legal-evidence.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.legal-evidence.store'), [
                'topic' => 'legal_nom_activation',
                'reference' => 'DOF 2026-01-15, artículo 123',
                'evidence_url' => 'https://dof.gob.mx/nota_detalle.php?codigo=123',
                'effective_at' => '2026-01-15',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.legal-evidence.revoke', $evidence), [
                'reason' => 'La publicación oficial fue sustituida.',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('legal_evidences', 1);
        $this->assertDatabaseHas('legal_evidences', [
            'id' => $evidence->id,
            'approved' => true,
            'revoked_at' => null,
        ]);
    }

    public function test_non_administrator_does_not_see_the_legal_evidence_menu_item(): void
    {
        $this->administrator();
        $user = User::factory()->create();
        $user->assignRole('Usuario');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertDontSee(route('admin.legal-evidence.index'), false);
    }

    public function test_revocation_screen_requires_explicit_confirmation(): void
    {
        $administrator = $this->administrator();
        $this->approvedEvidence($administrator, 'legal_nom_activation');

        $this->actingAs($administrator)
            ->get(route('admin.legal-evidence.index'))
            ->assertSee('return confirm(', false)
            ->assertSee('¿Confirma que desea revocar esta aprobación?', false);
    }

    public function test_legal_evidence_and_revocation_reason_are_escaped_in_the_screen(): void
    {
        $administrator = $this->administrator();
        $evidence = $this->approvedEvidence($administrator, 'legal_nom_activation');
        $evidence->reference = '<script>alert(1)</script>';
        $evidence->save();

        $this->actingAs($administrator)
            ->post(route('admin.legal-evidence.revoke', $evidence), [
                'reason' => '<script>alert(1)</script> publicación sustituida',
            ])
            ->assertRedirect(route('admin.legal-evidence.index'));

        $this->actingAs($administrator)
            ->get(route('admin.legal-evidence.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
