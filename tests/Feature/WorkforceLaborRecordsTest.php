<?php

namespace Tests\Feature;

use App\Models\CategoriaRelacionLaboral;
use App\Models\CentroTrabajo;
use App\Models\ModalidadContrato;
use App\Models\PlantillaContrato;
use App\Models\Puesto;
use App\Models\Trabajador;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Database\Seeders\CentroTrabajoSeeder;
use Database\Seeders\PuestoSeeder;
use Database\Seeders\SistemaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkforceLaborRecordsTest extends TestCase
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

    public function test_guest_is_redirected_to_login_for_workforce_routes(): void
    {
        $this->get(route('workforce.workers'))->assertRedirect('/login');
        $this->post(route('workforce.workers.store'))->assertRedirect('/login');
        $this->get(route('workforce.contracts'))->assertRedirect('/login');
        $this->post(route('workforce.contracts.store'))->assertRedirect('/login');
    }

    public function test_authorized_user_can_create_worker_with_personal_data(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $this->actingAs($user)
            ->post(route('workforce.workers.store'), [
                'numero_empleado' => 'EMP-001',
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'apellido_materno' => 'García',
                'curp' => 'PEGJ800101HDFRRN09',
                'nss' => '12345678901',
                'rfc' => 'PEGJ800101ABC',
                'fecha_nacimiento' => '1980-01-01',
                'fecha_ingreso' => '2024-01-15',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('trabajadores', [
            'centro_trabajo_id' => $centro?->id,
            'numero_empleado' => 'EMP-001',
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'curp' => 'PEGJ800101HDFRRN09',
            'nss' => '12345678901',
        ]);
    }

    public function test_worker_creation_is_denied_without_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario', attachCenter: false);

        $this->actingAs($user)
            ->post(route('workforce.workers.store'), [
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'curp' => 'PEGJ800101HDFRRN09',
                'nss' => '12345678901',
                'fecha_ingreso' => '2024-01-15',
            ])
            ->assertForbidden();
    }

    /**
     * @return array<string, array{0: string|null, 1: string, 2: string, 3: int}>
     */
    public static function workerWriteDenials(): array
    {
        return [
            'branch_manager_denied_personal_worker_data' => ['RESP-SUC', 'Usuario', 'workforce.workers.store', 403],
            'auditor_role_denied_worker_write' => [null, 'Auditor', 'workforce.workers.store', 403],
            'other_position_denied_worker_write' => ['COORD-OP', 'Usuario', 'workforce.workers.store', 403],
        ];
    }

    #[DataProvider('workerWriteDenials')]
    public function test_worker_write_is_denied_for_unauthorized_position_or_role(
        ?string $clavePuesto,
        string $roleName,
        string $routeName,
        int $expectedStatus,
    ): void {
        $user = $this->userWithPosition($clavePuesto, $roleName);

        $this->actingAs($user)
            ->post(route($routeName), [
                'numero_empleado' => 'EMP-002',
                'nombre' => 'Ana',
                'apellido_paterno' => 'López',
                'curp' => 'LOAA900101MDFPNN02',
                'nss' => '10987654321',
                'fecha_ingreso' => '2024-02-01',
            ])
            ->assertStatus($expectedStatus);
    }

    public function test_authorized_user_can_create_worker_contract_with_distinct_concepts(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();

        $trabajador = Trabajador::factory()->for($centro)->create();
        $plantilla = PlantillaContrato::factory()->create();
        $modalidad = ModalidadContrato::factory()->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();

        $this->actingAs($user)
            ->post(route('workforce.contracts.store'), [
                'trabajador_id' => $trabajador->id,
                'plantilla_contrato_id' => $plantilla->id,
                'modalidad_contrato_id' => $modalidad->id,
                'categoria_relacion_laboral_id' => $categoria->id,
                'fecha_inicio' => '2024-01-15',
                'fecha_fin' => '2025-01-14',
                'salario' => 15000.00,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('contratos_trabajador', [
            'trabajador_id' => $trabajador->id,
            'plantilla_contrato_id' => $plantilla->id,
            'modalidad_contrato_id' => $modalidad->id,
            'categoria_relacion_laboral_id' => $categoria->id,
        ]);

        $this->assertNotSame($plantilla->getTable(), $modalidad->getTable());
        $this->assertNotSame($modalidad->getTable(), $categoria->getTable());
        $this->assertNotSame($plantilla->getTable(), $categoria->getTable());
    }

    public function test_contract_creation_is_denied_for_worker_outside_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        app(CentroTrabajoContext::class)->activo();

        $otherCenter = CentroTrabajo::factory()->create();
        $trabajador = Trabajador::factory()->for($otherCenter)->create();
        $plantilla = PlantillaContrato::factory()->create();
        $modalidad = ModalidadContrato::factory()->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();

        $this->actingAs($user)
            ->post(route('workforce.contracts.store'), [
                'trabajador_id' => $trabajador->id,
                'plantilla_contrato_id' => $plantilla->id,
                'modalidad_contrato_id' => $modalidad->id,
                'categoria_relacion_laboral_id' => $categoria->id,
                'fecha_inicio' => '2024-01-15',
            ])
            ->assertNotFound();
    }
}
