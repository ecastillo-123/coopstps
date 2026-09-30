<?php

namespace Tests\Feature;

use App\Models\CentroTrabajo;
use App\Models\Puesto;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_can_view_and_manage_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $operationalUser = User::factory()->create();
        $operationalUser->assignRole('Usuario');

        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');

        $this->post(route('admin.users.store'))
            ->assertRedirect('/login');

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Usuarios y permisos')
            ->assertSee(route('admin.users.index'));

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertSee(route('admin.users.index'))
            ->assertSee('Usuarios y permisos');

        $this->actingAs($operationalUser)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($operationalUser)
            ->post(route('admin.users.store'))
            ->assertForbidden();

        $this->actingAs($operationalUser)
            ->get(route('dashboard'))
            ->assertDontSee(route('admin.users.index'))
            ->assertDontSee('Usuarios y permisos');

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');
    }

    public function test_administrator_creates_and_edits_a_user_with_profile_position_and_role(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();

        $this->actingAs($administrator)
            ->post(route('admin.users.store'), [
                'nombre' => 'Elena María',
                'apellido_paterno' => 'García',
                'apellido_materno' => 'López',
                'puesto_id' => $position->id,
                'email' => 'elena.garcia@coopstps.test',
                'telefono' => '3312345678',
                'role' => 'Usuario',
                'password' => 'Initial#Pass123',
                'password_confirmation' => 'Initial#Pass123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()->where('email', 'elena.garcia@coopstps.test')->firstOrFail();

        $this->assertSame('Elena María', $user->nombre);
        $this->assertSame('García', $user->apellido_paterno);
        $this->assertSame('López', $user->apellido_materno);
        $this->assertSame($position->id, $user->puesto_id);
        $this->assertSame('3312345678', $user->telefono);
        $this->assertSame(['Usuario'], $user->getRoleNames()->all());
        $this->assertTrue($user->hasPermissionTo('personal.modificar'));
        $this->assertTrue($user->centrosTrabajo()->whereKey(CentroTrabajo::query()->where('clave', 'CT-0001')->value('id'))->exists());

        $this->actingAs($administrator)
            ->put(route('admin.users.update', $user), [
                'nombre' => 'Elena',
                'apellido_paterno' => 'García',
                'apellido_materno' => null,
                'puesto_id' => $position->id,
                'email' => 'elena.garcia@coopstps.test',
                'telefono' => null,
                'role' => 'Auditor',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Elena', $user->nombre);
        $this->assertNull($user->apellido_materno);
        $this->assertNull($user->telefono);
        $this->assertSame(['Auditor'], $user->getRoleNames()->all());
        $this->assertFalse($user->hasPermissionTo('personal.modificar'));
    }

    public function test_administrator_can_reset_password_without_revealing_plaintext_and_user_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'account@coopstps.test',
            'password' => 'Old#Password123',
        ]);
        $user->centrosTrabajo()->attach(CentroTrabajo::query()->where('clave', 'CT-0001')->value('id'));
        $newPassword = 'New#SecurePass123';

        $response = $this->actingAs($administrator)
            ->put(route('admin.users.password.update', $user), [
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertDontSee($newPassword);

        $user->refresh();
        $this->assertNotSame($newPassword, $user->password);
        $this->assertTrue(Hash::check($newPassword, $user->password));

        $this->post(route('logout'));

        Livewire::test('pages::auth.login')
            ->set('email', $user->email)
            ->set('password', $newPassword)
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertStringNotContainsString($newPassword, $response->getContent());
    }

    public function test_administrator_can_reset_password_for_a_same_center_administrator(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $managedAdministrator = User::factory()->create(['password' => 'Old#Password123']);
        $managedAdministrator->assignRole('Administrador');
        $managedAdministrator->centrosTrabajo()->attach(CentroTrabajo::query()->where('clave', 'CT-0001')->value('id'));
        $newPassword = 'New#AdminPass123';

        $this->actingAs($administrator)
            ->put(route('admin.users.password.update', $managedAdministrator), [
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check($newPassword, $managedAdministrator->fresh()->password));
    }

    public function test_users_from_other_centers_are_omitted_from_the_list(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $otherCenter = CentroTrabajo::factory()->create();
        $otherCenterUser = User::factory()->create(['email' => 'other-center@coopstps.test']);
        $otherCenterUser->centrosTrabajo()->attach($otherCenter);

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee($otherCenterUser->email);
    }

    public function test_edit_and_password_panels_return_not_found_for_users_from_other_centers(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $otherCenter = CentroTrabajo::factory()->create();
        $otherCenterUser = User::factory()->create(['email' => 'other-center@coopstps.test']);
        $otherCenterUser->centrosTrabajo()->attach($otherCenter);

        $this->actingAs($administrator)
            ->get(route('admin.users.index', ['edit' => $otherCenterUser->id]))
            ->assertNotFound();

        $this->get(route('admin.users.index', ['password' => $otherCenterUser->id]))
            ->assertNotFound();
    }

    public function test_updates_and_password_resets_return_not_found_for_users_from_other_centers(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();
        $otherCenter = CentroTrabajo::factory()->create();
        $otherCenterUser = User::factory()->create([
            'nombre' => 'Unchanged',
            'email' => 'other-center@coopstps.test',
            'password' => 'Original#Password123',
        ]);
        $otherCenterUser->assignRole('Usuario');
        $otherCenterUser->centrosTrabajo()->attach($otherCenter);
        $originalPasswordHash = $otherCenterUser->password;

        $this->actingAs($administrator)
            ->put(route('admin.users.update', $otherCenterUser), [
                'nombre' => 'Changed',
                'apellido_paterno' => 'Account',
                'puesto_id' => $position->id,
                'email' => $otherCenterUser->email,
                'role' => 'Auditor',
            ])
            ->assertNotFound();

        $this->put(route('admin.users.password.update', $otherCenterUser), [
            'password' => 'Changed#Password123',
            'password_confirmation' => 'Changed#Password123',
        ])->assertNotFound();

        $otherCenterUser->refresh();
        $this->assertSame('Unchanged', $otherCenterUser->nombre);
        $this->assertSame('Usuario', $otherCenterUser->getRoleNames()->first());
        $this->assertSame($originalPasswordHash, $otherCenterUser->password);
    }

    public function test_account_creation_is_rejected_without_an_active_center(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $administrator->centrosTrabajo()->detach();
        app(CentroTrabajoContext::class)->reiniciar();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();

        $this->actingAs($administrator)
            ->post(route('admin.users.store'), [
                'nombre' => 'Orphan',
                'apellido_paterno' => 'Account',
                'puesto_id' => $position->id,
                'email' => 'orphan@coopstps.test',
                'role' => 'Usuario',
                'password' => 'Initial#Pass123',
                'password_confirmation' => 'Initial#Pass123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'orphan@coopstps.test']);
    }

    public function test_duplicate_email_is_rejected_without_creating_an_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();

        $this->actingAs($administrator)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'nombre' => 'Duplicate',
                'apellido_paterno' => 'Email',
                'puesto_id' => $position->id,
                'email' => 'admin@coopstps.test',
                'role' => 'Usuario',
                'password' => 'Initial#Pass123',
                'password_confirmation' => 'Initial#Pass123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();

        $this->actingAs($administrator)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'nombre' => 'Invalid',
                'apellido_paterno' => 'Email',
                'puesto_id' => $position->id,
                'email' => 'not-an-email',
                'role' => 'Usuario',
                'password' => 'Initial#Pass123',
                'password_confirmation' => 'Initial#Pass123',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_missing_required_account_fields_are_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();

        $this->actingAs($administrator)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['nombre', 'apellido_paterno', 'puesto_id', 'email', 'role', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_administrator_role_and_system_position_cannot_be_assigned_to_a_managed_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $systemPosition = Puesto::query()->where('clave', 'ADMIN-SIS')->firstOrFail();

        $this->actingAs($administrator)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'nombre' => 'Attempted',
                'apellido_paterno' => 'Escalation',
                'puesto_id' => $systemPosition->id,
                'email' => 'escalation@coopstps.test',
                'role' => 'Administrador',
                'password' => 'Initial#Pass123',
                'password_confirmation' => 'Initial#Pass123',
            ])
            ->assertSessionHasErrors(['puesto_id', 'role']);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('ADMIN-SIS', $administrator->puesto?->clave);
    }

    public function test_provisioned_operational_user_can_open_workforce_without_admin_position_bypass(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $position = Puesto::query()->where('clave', 'GER-RH')->firstOrFail();

        $this->actingAs($administrator)
            ->get(route('workforce.workers'))
            ->assertForbidden();

        $this->post(route('admin.users.store'), [
            'nombre' => 'María',
            'apellido_paterno' => 'Operativa',
            'puesto_id' => $position->id,
            'email' => 'operativa@coopstps.test',
            'role' => 'Usuario',
            'password' => 'Initial#Pass123',
            'password_confirmation' => 'Initial#Pass123',
        ])->assertRedirect(route('admin.users.index'));

        $operationalUser = User::query()->where('email', 'operativa@coopstps.test')->firstOrFail();
        $this->assertTrue($operationalUser->hasPermissionTo('personal.modificar'));
        $this->assertSame('GER-RH', $operationalUser->puesto?->clave);
        $this->assertDatabaseHas('centro_trabajo_user', [
            'user_id' => $operationalUser->id,
            'centro_trabajo_id' => CentroTrabajo::query()->where('clave', 'CT-0001')->value('id'),
        ]);

        $this->actingAs($operationalUser);
        app(CentroTrabajoContext::class)->reiniciar();

        $this->get(route('workforce.workers'))
            ->assertOk()
            ->assertSee('Trabajadores');
    }

    public function test_existing_user_with_null_optional_profile_fields_renders_and_can_log_in(): void
    {
        $this->seed(DatabaseSeeder::class);
        $administrator = User::query()->where('email', 'admin@coopstps.test')->firstOrFail();
        $existingUser = User::factory()->create([
            'nombre' => 'Legacy',
            'apellido_paterno' => 'Account',
            'apellido_materno' => null,
            'telefono' => null,
            'email' => 'legacy@coopstps.test',
            'password' => 'legacy-password',
        ]);
        $existingUser->centrosTrabajo()->attach(CentroTrabajo::query()->where('clave', 'CT-0001')->value('id'));

        $this->actingAs($administrator)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee($existingUser->email)
            ->assertSee('Legacy Account')
            ->assertSee('—');

        $this->post(route('logout'));

        Livewire::test('pages::auth.login')
            ->set('email', $existingUser->email)
            ->set('password', 'legacy-password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($existingUser);
    }
}
