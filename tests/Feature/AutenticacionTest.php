<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Administrador', 'web');
    }

    public function test_la_pantalla_de_login_se_renderiza(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sistema de Cumplimiento STPS');
    }

    public function test_un_invitado_es_redirigido_al_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_la_raiz_redirige_al_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_un_usuario_activo_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create([
            'email' => 'usuario@coopstps.test',
            'password' => 'secreto123',
            'activo' => true,
        ]);

        Livewire::test('pages::auth.login')
            ->set('email', 'usuario@coopstps.test')
            ->set('password', 'secreto123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_un_usuario_con_password_incorrecta_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'email' => 'usuario@coopstps.test',
            'password' => 'secreto123',
        ]);

        Livewire::test('pages::auth.login')
            ->set('email', 'usuario@coopstps.test')
            ->set('password', 'incorrecta')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_un_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        User::factory()->create([
            'email' => 'inactivo@coopstps.test',
            'password' => 'secreto123',
            'activo' => false,
        ]);

        Livewire::test('pages::auth.login')
            ->set('email', 'inactivo@coopstps.test')
            ->set('password', 'secreto123')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_el_login_exige_campos_obligatorios(): void
    {
        Livewire::test('pages::auth.login')
            ->set('email', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['email' => 'required', 'password' => 'required']);
    }

    public function test_un_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_un_usuario_autenticado_es_redirigido_del_login_al_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/dashboard');
    }
}
