<?php

namespace Tests\Feature;

use App\Models\CategoriaRelacionLaboral;
use App\Models\CentroTrabajo;
use App\Models\ContratoTrabajador;
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
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkforceSensitiveReadsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string|null, 1: string}>
     */
    public static function usersWithoutAllPersonalAccessDimensions(): array
    {
        return [
            'branch_manager_has_permission_but_ineligible_position' => ['RESP-SUC', 'Usuario'],
            'auditor_lacks_personal_permission_despite_eligible_position' => ['GER-RH', 'Auditor'],
            'administrator_role_without_position_is_not_eligible' => [null, 'Administrador'],
        ];
    }

    #[DataProvider('usersWithoutAllPersonalAccessDimensions')]
    public function test_user_without_all_access_dimensions_cannot_read_sensitive_workforce_data(
        ?string $positionKey,
        string $roleName,
    ): void {
        $user = $this->userWithPosition($positionKey, $roleName);
        $this->actingAs($user);
        $this->createSensitiveRecords(CentroTrabajo::factory()->create());

        $this->get(route('workforce.workers'))
            ->assertForbidden()
            ->assertDontSee('PEGJ800101HDFRRN09')
            ->assertDontSee('12345678901');

        $this->get(route('workforce.contracts'))
            ->assertForbidden()
            ->assertDontSee('$18,432.75');

        Livewire::actingAs($user)
            ->test('pages::workforce.workers')
            ->assertForbidden()
            ->assertDontSee(['PEGJ800101HDFRRN09', '12345678901']);

        Livewire::actingAs($user)
            ->test('pages::workforce.contracts')
            ->assertForbidden()
            ->assertDontSee('$18,432.75');
    }

    public function test_eligible_hr_user_without_active_center_cannot_read_sensitive_workforce_data(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario', attachCenter: false);
        $this->actingAs($user);
        $this->createSensitiveRecords(CentroTrabajo::factory()->create());

        $this->get(route('workforce.workers'))
            ->assertForbidden()
            ->assertDontSee('PEGJ800101HDFRRN09')
            ->assertDontSee('12345678901');

        $this->get(route('workforce.contracts'))
            ->assertForbidden()
            ->assertDontSee('$18,432.75');
    }

    public function test_authorized_hr_user_can_read_sensitive_workforce_data_only_for_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $activeCenter = app(CentroTrabajoContext::class)->activo();
        $this->assertNotNull($activeCenter);
        $this->createSensitiveRecords($activeCenter);

        $otherCenter = CentroTrabajo::factory()->create();
        $this->createSensitiveRecords($otherCenter, [
            'curp' => 'LOAA900101MDFPNN02',
            'nss' => '10987654321',
            'salary' => 98765.43,
        ]);

        $this->get(route('workforce.workers'))
            ->assertSee('PEGJ800101HDFRRN09')
            ->assertSee('12345678901')
            ->assertDontSee('LOAA900101MDFPNN02')
            ->assertDontSee('10987654321');

        $this->get(route('workforce.contracts'))
            ->assertSee('$18,432.75')
            ->assertDontSee('$98,765.43');

        Livewire::actingAs($user)
            ->test('pages::workforce.workers')
            ->assertSee('PEGJ800101HDFRRN09')
            ->assertSee('12345678901')
            ->assertDontSee('LOAA900101MDFPNN02')
            ->assertDontSee('10987654321');

        Livewire::actingAs($user)
            ->test('pages::workforce.contracts')
            ->assertSee('$18,432.75')
            ->assertDontSee('$98,765.43');
    }

    public function test_livewire_update_rechecks_access_before_rendering_sensitive_workforce_data(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $activeCenter = app(CentroTrabajoContext::class)->activo();
        $this->assertNotNull($activeCenter);
        $this->createSensitiveRecords($activeCenter);

        $workers = Livewire::actingAs($user)
            ->test('pages::workforce.workers')
            ->assertSee('PEGJ800101HDFRRN09')
            ->assertSee('12345678901');
        $contracts = Livewire::actingAs($user)
            ->test('pages::workforce.contracts')
            ->assertSee('$18,432.75');

        $user->forceFill(['puesto_id' => null])->save();
        $user->unsetRelation('puesto');

        $workers->call('$refresh')
            ->assertForbidden()
            ->assertDontSee(['PEGJ800101HDFRRN09', '12345678901']);
        $contracts->call('$refresh')
            ->assertForbidden()
            ->assertDontSee('$18,432.75');
    }

    private function userWithPosition(?string $positionKey, string $roleName, bool $attachCenter = true): User
    {
        $this->seed([
            SistemaSeeder::class,
            PuestoSeeder::class,
            CentroTrabajoSeeder::class,
        ]);

        $positionId = $positionKey !== null
            ? Puesto::where('clave', $positionKey)->value('id')
            : null;

        $user = User::factory()->create([
            'puesto_id' => $positionId,
        ]);
        $user->assignRole($roleName);

        if ($attachCenter) {
            $user->centrosTrabajo()->attach(CentroTrabajo::factory()->create());
        }

        return $user;
    }

    /**
     * @param  array{curp?: string, nss?: string, salary?: float}  $sensitiveData
     */
    private function createSensitiveRecords(CentroTrabajo $center, array $sensitiveData = []): void
    {
        $worker = Trabajador::factory()->for($center)->create([
            'curp' => $sensitiveData['curp'] ?? 'PEGJ800101HDFRRN09',
            'nss' => $sensitiveData['nss'] ?? '12345678901',
        ]);

        ContratoTrabajador::factory()->for($worker)->create([
            'plantilla_contrato_id' => PlantillaContrato::factory(),
            'modalidad_contrato_id' => ModalidadContrato::factory(),
            'categoria_relacion_laboral_id' => CategoriaRelacionLaboral::factory(),
            'salario' => $sensitiveData['salary'] ?? 18432.75,
        ]);
    }
}
