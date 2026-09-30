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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function test_lifecycle_changes_record_ingreso_reingreso_and_baja_and_update_current_worker_state(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create([
            'activo' => true,
            'fecha_ingreso' => '2020-01-01',
        ]);
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoria->id,
        ))->assertCreated();

        $trabajador->refresh();
        $this->assertTrue($trabajador->activo);
        $this->assertSame('2024-01-10', $trabajador->fecha_ingreso->toDateString());

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'baja',
            '2024-02-10',
            null,
        ))->assertCreated();

        $trabajador->refresh();
        $this->assertFalse($trabajador->activo);
        $this->assertSame('2024-01-10', $trabajador->fecha_ingreso->toDateString());

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'reingreso',
            '2024-03-10',
            $categoria->id,
        ))->assertCreated();

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'baja',
            '2024-02-15',
            null,
        ))->assertCreated();

        $trabajador->refresh();
        $this->assertTrue($trabajador->activo);
        $this->assertSame('2024-03-10', $trabajador->fecha_ingreso->toDateString());
        $this->assertSame(
            ['ingreso', 'baja', 'baja', 'reingreso'],
            DB::table('cambios_ciclo_laboral')
                ->where('trabajador_id', $trabajador->id)
                ->orderBy('fecha_evento')
                ->pluck('tipo')
                ->all(),
        );
        $this->assertDatabaseHas('cambios_ciclo_laboral', [
            'trabajador_id' => $trabajador->id,
            'centro_trabajo_id' => $centro->id,
            'tipo' => 'ingreso',
            'categoria_relacion_laboral_id' => $categoria->id,
        ]);
        $this->assertDatabaseCount('evidencias_ciclo_laboral', 4);
    }

    public function test_lifecycle_category_stays_distinct_from_contract_template_and_modality(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create();
        $plantilla = PlantillaContrato::factory()->create();
        $modalidad = ModalidadContrato::factory()->create();
        $categoriaContrato = CategoriaRelacionLaboral::factory()->create();
        $categoriaEvento = CategoriaRelacionLaboral::factory()->create();
        $contrato = ContratoTrabajador::factory()->for($trabajador)->create([
            'plantilla_contrato_id' => $plantilla->id,
            'modalidad_contrato_id' => $modalidad->id,
            'categoria_relacion_laboral_id' => $categoriaContrato->id,
        ]);
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoriaEvento->id,
        ) + [
            'plantilla_contrato_id' => $plantilla->id,
            'modalidad_contrato_id' => $modalidad->id,
        ])->assertCreated();

        $this->assertDatabaseHas('cambios_ciclo_laboral', [
            'trabajador_id' => $trabajador->id,
            'categoria_relacion_laboral_id' => $categoriaEvento->id,
        ]);
        $this->assertDatabaseHas('contratos_trabajador', [
            'id' => $contrato->id,
            'plantilla_contrato_id' => $plantilla->id,
            'modalidad_contrato_id' => $modalidad->id,
            'categoria_relacion_laboral_id' => $categoriaContrato->id,
        ]);
    }

    public function test_lifecycle_evidence_is_private_downloadable_and_cannot_be_deleted(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoria->id,
        ))->assertCreated();

        $cambio = DB::table('cambios_ciclo_laboral')->first();
        $evidencia = DB::table('evidencias_ciclo_laboral')->first();
        $this->assertNotNull($evidencia);
        $this->assertDatabaseHas('evidencias_ciclo_laboral', [
            'id' => $evidencia->id,
            'trabajador_id' => $trabajador->id,
            'centro_trabajo_id' => $centro->id,
            'cambio_ciclo_laboral_id' => $cambio->id,
            'nombre_original' => 'evidence.png',
        ]);
        Storage::disk('local')->assertExists($evidencia->ruta);
        Storage::disk('public')->assertMissing($evidencia->ruta);

        $this->get('/workforce/workers/'.$trabajador->id.'/lifecycle-evidence/'.$evidencia->id)
            ->assertDownload('evidence.png');

        $this->delete('/workforce/workers/'.$trabajador->id.'/lifecycle-evidence/'.$evidencia->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('evidencias_ciclo_laboral', ['id' => $evidencia->id]);
        Storage::disk('local')->assertExists($evidencia->ruta);

        $auditor = User::factory()->create([
            'puesto_id' => Puesto::where('clave', 'AUD-STPS')->value('id'),
        ]);
        $auditor->assignRole('Auditor');
        $auditor->centrosTrabajo()->attach($centro);
        app(CentroTrabajoContext::class)->reiniciar();

        $this->actingAs($auditor)
            ->get('/workforce/workers/'.$trabajador->id.'/lifecycle-evidence/'.$evidencia->id)
            ->assertForbidden();
    }

    public function test_lifecycle_evidence_download_is_restricted_to_the_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $otroCentro = CentroTrabajo::factory()->create();
        $user->centrosTrabajo()->attach($otroCentro);
        $trabajador = Trabajador::factory()->for($centro)->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoria->id,
        ))->assertCreated();

        $evidencia = DB::table('evidencias_ciclo_laboral')->first();
        app(CentroTrabajoContext::class)->reiniciar();

        $this->withSession(['centro_trabajo_activo_id' => $otroCentro->id])
            ->get('/workforce/workers/'.$trabajador->id.'/lifecycle-evidence/'.$evidencia->id)
            ->assertNotFound();
    }

    /**
     * @return array<string, array{0: string|null, 1: string}>
     */
    public static function lifecycleWriteDenials(): array
    {
        return [
            'branch_manager_cannot_write_personal_lifecycle' => ['RESP-SUC', 'Usuario'],
            'auditor_cannot_write_personal_lifecycle' => ['AUD-STPS', 'Auditor'],
            'administrator_without_eligible_position_cannot_write_personal_lifecycle' => [null, 'Administrador'],
        ];
    }

    #[DataProvider('lifecycleWriteDenials')]
    public function test_lifecycle_write_is_denied_for_unauthorized_position_or_role(
        ?string $clavePuesto,
        string $roleName,
    ): void {
        $user = $this->userWithPosition($clavePuesto, $roleName);
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoria->id,
        ))->assertForbidden();

        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
    }

    public function test_lifecycle_write_is_denied_without_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario', attachCenter: false);
        $trabajador = Trabajador::factory()->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->actingAs($user)
            ->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
                'ingreso',
                '2024-01-10',
                $categoria->id,
            ))
            ->assertForbidden();

        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
    }

    public function test_lifecycle_write_cannot_target_a_worker_outside_the_active_center(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        $this->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
            'ingreso',
            '2024-01-10',
            $categoria->id,
        ))->assertNotFound();

        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
    }

    public function test_lifecycle_ingreso_requires_a_relationship_category(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create();
        Storage::fake('local');

        $this->from(route('workforce.workers'))
            ->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
                'ingreso',
                '2024-01-10',
                null,
            ))
            ->assertSessionHasErrors('categoria_relacion_laboral_id');

        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
    }

    public function test_future_lifecycle_event_is_rejected_without_persisting_or_changing_worker_state(): void
    {
        $this->freezeTime();
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $fechaIngresoActual = now()->subYear()->toDateString();
        $trabajador = Trabajador::factory()->for($centro)->create([
            'activo' => true,
            'fecha_ingreso' => $fechaIngresoActual,
        ]);
        Storage::fake('local');

        $this->from(route('workforce.workers'))
            ->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $this->lifecyclePayload(
                'baja',
                now()->addDay()->toDateString(),
                null,
            ))
            ->assertSessionHasErrors('fecha_evento');

        $trabajador->refresh();
        $this->assertTrue($trabajador->activo);
        $this->assertSame($fechaIngresoActual, $trabajador->fecha_ingreso->toDateString());
        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
        $this->assertDatabaseCount('evidencias_ciclo_laboral', 0);
        Storage::disk('local')->assertDirectoryEmpty('worker-lifecycle/'.$centro->id.'/'.$trabajador->id);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: int}>
     */
    public static function invalidLifecycleEvidence(): array
    {
        return [
            'active_svg_is_rejected' => ['evidence.svg', 'image/svg+xml', 20],
            'evidence_over_five_megabytes_is_rejected' => ['evidence.png', 'image/png', 5121],
        ];
    }

    #[DataProvider('invalidLifecycleEvidence')]
    public function test_lifecycle_upload_rejects_unsafe_or_oversized_evidence(
        string $filename,
        string $mimeType,
        int $sizeKilobytes,
    ): void {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajador = Trabajador::factory()->for($centro)->create();
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');
        $payload = $this->lifecyclePayload('ingreso', '2024-01-10', $categoria->id);
        $payload['evidencias'] = [UploadedFile::fake()->create($filename, $sizeKilobytes, $mimeType)];

        $this->from(route('workforce.workers'))
            ->post('/workforce/workers/'.$trabajador->id.'/lifecycle', $payload)
            ->assertSessionHasErrors('evidencias.0');

        $this->assertDatabaseCount('cambios_ciclo_laboral', 0);
        Storage::disk('local')->assertDirectoryEmpty('worker-lifecycle/'.$centro->id.'/'.$trabajador->id);
    }

    public function test_workers_are_filtered_by_lifecycle_state_date_and_position(): void
    {
        $user = $this->userWithPosition('GER-RH', 'Usuario');
        $this->actingAs($user);
        $centro = app(CentroTrabajoContext::class)->activo();
        $puesto = Puesto::where('clave', 'JEF-SST')->firstOrFail();
        $otroPuesto = Puesto::where('clave', 'ANL-RH')->firstOrFail();
        $trabajadorFiltrado = Trabajador::factory()->for($centro)->create([
            'nombre' => 'Filtrado',
            'apellido_paterno' => 'Correcto',
            'puesto_id' => $puesto->id,
            'activo' => false,
        ]);
        $trabajadorFueraDeFecha = Trabajador::factory()->for($centro)->create([
            'nombre' => 'Fuera',
            'apellido_paterno' => 'Fecha',
            'puesto_id' => $puesto->id,
            'activo' => false,
        ]);
        $trabajadorFueraDePuesto = Trabajador::factory()->for($centro)->create([
            'nombre' => 'Fuera',
            'apellido_paterno' => 'Puesto',
            'puesto_id' => $otroPuesto->id,
            'activo' => false,
        ]);
        $categoria = CategoriaRelacionLaboral::factory()->create();
        Storage::fake('local');

        foreach ([[$trabajadorFiltrado, '2024-02-01'], [$trabajadorFueraDeFecha, '2024-01-01'], [$trabajadorFueraDePuesto, '2024-02-01']] as [$worker, $eventDate]) {
            $this->post('/workforce/workers/'.$worker->id.'/lifecycle', $this->lifecyclePayload('baja', $eventDate, $categoria->id))
                ->assertCreated();
        }

        $this->get(route('workforce.workers', [
            'estado' => 'baja',
            'puesto_id' => $puesto->id,
            'fecha_desde' => '2024-02-01',
            'fecha_hasta' => '2024-02-01',
        ]))
            ->assertSee('Filtrado Correcto')
            ->assertDontSee('Fuera Fecha')
            ->assertDontSee('Fuera Puesto');
    }

    /**
     * @return array{tipo: string, fecha_evento: string, categoria_relacion_laboral_id: int|null, evidencias: array<int, UploadedFile>}
     */
    private function lifecyclePayload(string $tipo, string $fechaEvento, ?int $categoriaId): array
    {
        return [
            'tipo' => $tipo,
            'fecha_evento' => $fechaEvento,
            'categoria_relacion_laboral_id' => $categoriaId,
            'evidencias' => [UploadedFile::fake()->image('evidence.png')],
        ];
    }
}
