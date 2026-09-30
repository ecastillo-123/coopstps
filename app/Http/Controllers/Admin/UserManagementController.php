<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    /** @var list<string> */
    private const ASSIGNABLE_ROLES = ['Usuario', 'Auditor'];

    public function store(Request $request, CentroTrabajoContext $context): RedirectResponse
    {
        $activeCenter = $context->activo();
        abort_if($activeCenter === null, 403);

        $data = $request->validate([
            ...$this->profileRules(),
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        DB::transaction(function () use ($data, $activeCenter): void {
            $user = User::create([
                'nombre' => $data['nombre'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'puesto_id' => $data['puesto_id'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'password' => $data['password'],
            ]);

            $user->syncRoles($this->assignableRole($data['role']));
            $user->centrosTrabajo()->sync([$activeCenter->getKey()]);
        });

        return to_route('admin.users.index')->with('status', 'La cuenta se creó correctamente.');
    }

    public function update(Request $request, string $user, CentroTrabajoContext $context): RedirectResponse
    {
        $user = $this->findUserInActiveCenter($user, $context);
        abort_if($user->hasRole('Administrador'), 403);

        $data = $request->validate($this->profileRules($user));

        DB::transaction(function () use ($data, $user): void {
            $user->update([
                'nombre' => $data['nombre'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'puesto_id' => $data['puesto_id'],
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
            ]);

            $user->syncRoles($this->assignableRole($data['role']));
        });

        return to_route('admin.users.index')->with('status', 'La cuenta se actualizó correctamente.');
    }

    public function updatePassword(Request $request, string $user, CentroTrabajoContext $context): RedirectResponse
    {
        $user = $this->findUserInActiveCenter($user, $context);

        $data = $request->validate([
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols(), 'confirmed'],
        ]);

        $user->password = $data['password'];
        $user->setRememberToken(Str::random(60));
        $user->save();

        return to_route('admin.users.index')->with('status', 'La contraseña se actualizó correctamente.');
    }

    private function findUserInActiveCenter(string $userId, CentroTrabajoContext $context): User
    {
        $activeCenter = $context->activo();
        abort_if($activeCenter === null, 404);

        return $activeCenter->users()->findOrFail($userId);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function profileRules(?User $user = null): array
    {
        $emailRule = Rule::unique('users', 'email');

        if ($user !== null) {
            $emailRule->ignore($user);
        }

        return [
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'puesto_id' => [
                'required',
                'integer',
                Rule::exists('puestos', 'id')->where('activo', true)->whereNotIn('clave', ['ADMIN-SIS']),
            ],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'telefono' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(self::ASSIGNABLE_ROLES)],
        ];
    }

    private function assignableRole(string $name): Role
    {
        return Role::query()
            ->where('name', $name)
            ->where('guard_name', 'web')
            ->whereIn('name', self::ASSIGNABLE_ROLES)
            ->firstOrFail();
    }
}
