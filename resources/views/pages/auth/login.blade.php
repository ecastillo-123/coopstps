<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::guest')] #[Title('Iniciar sesión')] class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $credenciales = [
            'email' => $this->email,
            'password' => $this->password,
            'activo' => true,
        ];

        if (! Auth::attempt($credenciales, $this->remember)) {
            $this->reset('password');

            $this->addError('email', 'Las credenciales no coinciden con nuestros registros.');

            return;
        }

        session()->regenerate();

        $this->redirectIntended(route('dashboard'), navigate: true);
    }
};
?>

<div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">

    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-lg">
                ST
            </span>
            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">
                Sistema de Cumplimiento STPS
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Ingresa con tu cuenta para acceder al panel de cumplimiento.
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form wire:submit="login" class="space-y-5">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Correo electrónico
                    </label>
                    <input id="email"
                           type="email"
                           wire:model="email"
                           autocomplete="username"
                           autofocus
                           required
                           class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Contraseña
                    </label>
                    <input id="password"
                           type="password"
                           wire:model="password"
                           autocomplete="current-password"
                           required
                           class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none">
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox"
                           wire:model="remember"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Mantener la sesión iniciada
                </label>

                <button type="submit"
                        class="flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-60"
                        wire:loading.attr="disabled"
                        wire:target="login">
                    <span wire:loading.remove wire:target="login">Entrar</span>
                    <span wire:loading wire:target="login">Verificando…</span>
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            El acceso es mediante cuentas provisionadas por el administrador del sistema.
        </p>
    </div>
</div>
