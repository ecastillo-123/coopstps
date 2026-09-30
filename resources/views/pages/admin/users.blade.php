<?php

use App\Models\Puesto;
use App\Models\User;
use App\Support\CentroTrabajoContext;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts::app')] #[Title('Usuarios y permisos')] class extends Component {
    public function boot(): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);
    }

    /** @return Collection<int, User> */
    #[Computed]
    public function users(): Collection
    {
        $activeCenter = app(CentroTrabajoContext::class)->activo();

        if ($activeCenter === null) {
            return new Collection();
        }

        return $activeCenter->users()
            ->with(['puesto', 'roles'])
            ->orderBy('nombre')
            ->orderBy('apellido_paterno')
            ->get();
    }

    /** @return Collection<int, Puesto> */
    #[Computed]
    public function positions(): Collection
    {
        return Puesto::query()
            ->where('activo', true)
            ->where('clave', '!=', 'ADMIN-SIS')
            ->orderBy('nombre')
            ->get();
    }

    /** @return Collection<int, Role> */
    #[Computed]
    public function assignableRoles(): Collection
    {
        return Role::query()
            ->with('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', ['Usuario', 'Auditor'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function editedUser(): ?User
    {
        $id = request()->query('edit');

        if (! is_string($id) || ! ctype_digit($id)) {
            return null;
        }

        $activeCenter = app(CentroTrabajoContext::class)->activo();
        abort_if($activeCenter === null, 404);

        return $activeCenter->users()->with(['puesto', 'roles'])->findOrFail($id);
    }

    #[Computed]
    public function passwordUser(): ?User
    {
        $id = request()->query('password');

        if (! is_string($id) || ! ctype_digit($id)) {
            return null;
        }

        $activeCenter = app(CentroTrabajoContext::class)->activo();
        abort_if($activeCenter === null, 404);

        return $activeCenter->users()->findOrFail($id);
    }
};
?>

<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Usuarios y permisos</h1>
        <p class="mt-1 text-sm text-slate-600">Crea cuentas operativas, asigna un puesto del catálogo y elige un rol del sistema. El puesto de negocio y el rol son asignaciones independientes.</p>
    </div>

    @if (session('status'))
        <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(20rem,0.8fr)_minmax(0,1.2fr)]">
        <section class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ $this->editedUser ? 'Editar cuenta' : 'Registrar usuario' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">La cuenta nueva se asignará únicamente al centro activo. Seleccione un centro para crear cuentas.</p>
            </div>

            <form method="POST" action="{{ $this->editedUser ? route('admin.users.update', $this->editedUser) : route('admin.users.store') }}" class="flex flex-col gap-4">
                @csrf
                @if ($this->editedUser)
                    @method('PUT')
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-sm font-medium text-slate-700">
                        <span class="mb-1 block">Nombre(s)</span>
                        <input name="nombre" value="{{ old('nombre', $this->editedUser?->nombre) }}" required maxlength="255" autocomplete="given-name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        @error('nombre') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        <span class="mb-1 block">Apellido paterno</span>
                        <input name="apellido_paterno" value="{{ old('apellido_paterno', $this->editedUser?->apellido_paterno) }}" required maxlength="255" autocomplete="family-name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        @error('apellido_paterno') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        <span class="mb-1 block">Apellido materno</span>
                        <input name="apellido_materno" value="{{ old('apellido_materno', $this->editedUser?->apellido_materno) }}" maxlength="255" autocomplete="additional-name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        @error('apellido_materno') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700">
                        <span class="mb-1 block">Puesto</span>
                        <select name="puesto_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                            <option value="">Seleccione un puesto</option>
                            @foreach ($this->positions as $position)
                                <option value="{{ $position->id }}" @selected((string) old('puesto_id', $this->editedUser?->puesto_id) === (string) $position->id)>{{ $position->nombre }}</option>
                            @endforeach
                        </select>
                        @error('puesto_id') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                        <span class="mb-1 block">Correo electrónico</span>
                        <input name="email" type="email" value="{{ old('email', $this->editedUser?->email) }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        @error('email') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                        <span class="mb-1 block">Teléfono</span>
                        <input name="telefono" type="tel" value="{{ old('telefono', $this->editedUser?->telefono) }}" maxlength="30" autocomplete="tel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        @error('telefono') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-sm font-medium text-slate-700 sm:col-span-2">
                        <span class="mb-1 block">Rol del sistema</span>
                        <select name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                            <option value="">Seleccione un rol</option>
                            @foreach ($this->assignableRoles as $role)
                                <option value="{{ $role->name }}" @selected(old('role', $this->editedUser?->getRoleNames()->first()) === $role->name)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <span class="mt-1 block text-xs font-normal text-slate-500">El rol determina los permisos existentes. El rol Administrador está reservado y no se puede asignar desde aquí.</span>
                        @error('role') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                    </label>
                </div>

                @unless ($this->editedUser)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="text-sm font-medium text-slate-700">
                            <span class="mb-1 block">Contraseña inicial</span>
                            <input name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                            @error('password') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="text-sm font-medium text-slate-700">
                            <span class="mb-1 block">Confirmar contraseña</span>
                            <input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                        </label>
                    </div>
                @endunless

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        {{ $this->editedUser ? 'Guardar cambios' : 'Guardar usuario' }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</a>
                </div>
            </form>

            <div class="border-t border-slate-200 pt-4">
                <h3 class="text-sm font-semibold text-slate-800">Permisos existentes por rol</h3>
                <div class="mt-2 flex flex-col gap-2">
                    @forelse ($this->assignableRoles as $role)
                        <details class="rounded-lg bg-slate-50 px-3 py-2">
                            <summary class="cursor-pointer text-sm font-medium text-slate-700">{{ $role->name }}</summary>
                            <ul class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($role->permissions as $permission)
                                    <li class="rounded bg-white px-2 py-1 text-xs text-slate-600">{{ $permission->name }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @empty
                        <p class="text-sm text-slate-500">No hay roles disponibles. Sincroniza el catálogo del sistema antes de asignar accesos.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Usuarios agregados</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Usuario</th>
                            <th scope="col" class="px-4 py-3 font-medium">Puesto</th>
                            <th scope="col" class="px-4 py-3 font-medium">Rol</th>
                            <th scope="col" class="px-4 py-3 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($this->users as $user)
                            <tr class="align-top">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $user->nombre_completo ?: '—' }}</p>
                                    <p class="text-slate-600">{{ $user->email }}</p>
                                    <p class="text-slate-500">{{ $user->telefono ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->puesto?->nombre ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->getRoleNames()->join(', ') ?: '—' }}</td>
                                <td class="min-w-40 px-4 py-3">
                                    <div class="flex flex-col items-start gap-2">
                                        @unless ($user->hasRole('Administrador'))
                                            <a href="{{ route('admin.users.index', ['edit' => $user->id]) }}" class="font-medium text-indigo-700 underline hover:text-indigo-900">Editar datos y rol</a>
                                        @else
                                            <span class="text-xs text-slate-500">Cuenta de sistema protegida</span>
                                        @endunless
                                        <a href="{{ route('admin.users.index', ['password' => $user->id]) }}" class="font-medium text-indigo-700 underline hover:text-indigo-900">Cambiar contraseña</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Todavía no hay usuarios registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if ($this->passwordUser)
        <section class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Cambiar contraseña</h2>
                <p class="mt-1 text-sm text-slate-600">Cuenta: {{ $this->passwordUser->email }}. La contraseña se almacena como hash y no se muestra después de guardarla.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.password.update', $this->passwordUser) }}" class="grid gap-4 sm:grid-cols-2">
                @csrf
                @method('PUT')
                <label class="text-sm font-medium text-slate-700">
                    <span class="mb-1 block">Nueva contraseña</span>
                    <input name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                    @error('password') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                </label>
                <label class="text-sm font-medium text-slate-700">
                    <span class="mb-1 block">Confirmar contraseña</span>
                    <input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal text-slate-900">
                </label>
                <div class="flex flex-wrap gap-2 sm:col-span-2">
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">Guardar contraseña</button>
                    <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </section>
    @endif
</div>
