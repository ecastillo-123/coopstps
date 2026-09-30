<?php

namespace Tests\Feature;

use App\Models\CentroTrabajo;
use App\Models\Puesto;
use App\Models\User;
use Database\Seeders\CentroTrabajoSeeder;
use Database\Seeders\PuestoSeeder;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleScopedAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rutas sensibles bajo prueba y el verbo HTTP que usa cada una.
     *
     * @var array<string, string>
     */
    private const VERBOS = [
        'personal.register' => 'post',
        'personal.modify' => 'put',
        'personal.delete' => 'post',
        'personal.approve' => 'post',
        'nom035.register' => 'post',
        'nom035.modify' => 'put',
        'audit-inspection.write' => 'post',
        'reports.export' => 'post',
    ];

    private function seedCatalogs(): void
    {
        $this->seed([
            SistemaSeeder::class,
            PuestoSeeder::class,
            CentroTrabajoSeeder::class,
        ]);
    }

    private function userWithPosition(?string $clavePuesto, string $roleName, bool $attachCenter = true): User
    {
        $this->seedCatalogs();

        $puestoId = $clavePuesto !== null
            ? Puesto::where('clave', $clavePuesto)->value('id')
            : null;

        $user = User::factory()->create([
            'puesto_id' => $puestoId,
        ]);

        $user->assignRole($roleName);

        if ($attachCenter) {
            $user->centrosTrabajo()->attach(CentroTrabajo::factory()->create());
        }

        return $user;
    }

    public function test_guest_is_redirected_to_login_for_sensitive_routes(): void
    {
        $this->post(route('personal.register'))
            ->assertRedirect('/login');
    }

    /**
     * @return array<int, array{0: string|null, 1: string, 2: string, 3: int}>
     */
    public static function matrixDeAcceso(): array
    {
        return [
            'gerente_rh_can_register_personal' => ['GER-RH', 'Usuario', 'personal.register', 200],
            'gerente_rh_can_modify_personal' => ['GER-RH', 'Usuario', 'personal.modify', 200],
            'gerente_rh_can_register_nom035' => ['GER-RH', 'Usuario', 'nom035.register', 200],
            'gerente_rh_can_modify_nom035' => ['GER-RH', 'Usuario', 'nom035.modify', 200],

            'analista_rh_can_register_personal' => ['ANL-RH', 'Usuario', 'personal.register', 200],
            'analista_rh_can_modify_personal' => ['ANL-RH', 'Usuario', 'personal.modify', 200],
            'analista_rh_can_register_nom035' => ['ANL-RH', 'Usuario', 'nom035.register', 200],
            'analista_rh_can_modify_nom035' => ['ANL-RH', 'Usuario', 'nom035.modify', 200],

            'gerente_general_can_register_personal' => ['GER-GEN', 'Usuario', 'personal.register', 200],
            'gerente_general_can_modify_personal' => ['GER-GEN', 'Usuario', 'personal.modify', 200],
            'gerente_general_can_register_nom035' => ['GER-GEN', 'Usuario', 'nom035.register', 200],
            'gerente_general_can_modify_nom035' => ['GER-GEN', 'Usuario', 'nom035.modify', 200],

            'responsable_sucursal_cannot_register_personal' => ['RESP-SUC', 'Usuario', 'personal.register', 403],
            'responsable_sucursal_cannot_modify_personal' => ['RESP-SUC', 'Usuario', 'personal.modify', 403],
            'responsable_sucursal_can_register_nom035' => ['RESP-SUC', 'Usuario', 'nom035.register', 200],
            'responsable_sucursal_can_modify_nom035' => ['RESP-SUC', 'Usuario', 'nom035.modify', 200],

            'other_position_denied_personal' => ['COORD-OP', 'Usuario', 'personal.register', 403],
            'other_position_denied_nom035' => ['COORD-OP', 'Usuario', 'nom035.register', 403],

            'auditor_stps_position_denied_personal' => ['AUD-STPS', 'Usuario', 'personal.register', 403],
            'auditor_stps_position_denied_nom035' => ['AUD-STPS', 'Usuario', 'nom035.register', 403],

            'no_position_denied_personal' => [null, 'Usuario', 'personal.register', 403],
            'no_position_denied_nom035' => [null, 'Usuario', 'nom035.register', 403],

            'administrator_role_does_not_bypass_personal' => [null, 'Administrador', 'personal.register', 403],
            'administrator_role_does_not_bypass_nom035' => [null, 'Administrador', 'nom035.register', 403],

            'usuario_role_does_not_bypass_personal' => [null, 'Usuario', 'personal.register', 403],
            'auditor_role_does_not_bypass_personal' => [null, 'Auditor', 'personal.register', 403],

            'auditor_role_can_write_audit_inspection_without_position' => [null, 'Auditor', 'audit-inspection.write', 200],
            'auditor_role_can_write_audit_inspection_with_hr_position' => ['GER-RH', 'Auditor', 'audit-inspection.write', 200],
            'hr_position_with_non_auditor_role_denied_audit_inspection' => ['GER-RH', 'Usuario', 'audit-inspection.write', 403],

            'authorized_user_can_export_reports' => ['GER-RH', 'Usuario', 'reports.export', 200],
            'user_without_export_permission_cannot_export' => ['GER-RH', 'Auditor', 'reports.export', 403],

            'register_grant_does_not_imply_delete' => ['GER-RH', 'Usuario', 'personal.delete', 403],
            'register_grant_does_not_imply_approval' => ['GER-RH', 'Usuario', 'personal.approve', 403],
        ];
    }

    #[DataProvider('matrixDeAcceso')]
    public function test_position_data_class_action_matrix(
        ?string $clavePuesto,
        string $roleName,
        string $routeName,
        int $expectedStatus,
    ): void {
        $user = $this->userWithPosition($clavePuesto, $roleName);

        $this->actingAs($user)
            ->{self::VERBOS[$routeName]}(route($routeName))
            ->assertStatus($expectedStatus);
    }

    public function test_eligible_position_without_active_center_is_denied(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario', attachCenter: false);

        $this->actingAs($user)
            ->post(route('personal.register'))
            ->assertForbidden();
    }

    public function test_export_is_denied_without_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario', attachCenter: false);

        $this->actingAs($user)
            ->post(route('reports.export'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_for_training_and_audit_routes(): void
    {
        $this->get(route('training.courses'))->assertRedirect('/login');
        $this->post(route('training.courses.store'))->assertRedirect('/login');
        $this->get(route('audit.inspections'))->assertRedirect('/login');
        $this->post(route('audit.inspections.store'))->assertRedirect('/login');
    }

    public function test_auditor_is_denied_training_write(): void
    {
        $user = $this->userWithPosition(null, 'Auditor');

        $this->actingAs($user)
            ->post(route('training.courses.store'), [
                'nombre' => 'Curso de alturas',
            ])
            ->assertForbidden();
    }

    public function test_non_auditor_role_is_denied_audit_inspection_write(): void
    {
        $user = $this->userWithPosition(null, 'Usuario');

        $this->actingAs($user)
            ->post(route('audit-inspection.write'))
            ->assertForbidden();
    }
}
