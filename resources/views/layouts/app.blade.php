@php
    use App\Support\CentroTrabajoContext;
    use Illuminate\Support\Facades\Route;

    $contexto = app(CentroTrabajoContext::class);
    $centroActivo = $contexto->activo();
    $centrosDisponibles = $contexto->disponibles();
    $modulos = config('sistema.modulos');
    $rutaActual = request()->route()?->getName();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-100 font-sans antialiased">
<div x-data="{ menu: false }" class="min-h-full">

    {{-- Barra superior móvil --}}
    <div class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4 lg:hidden">
        <button type="button" @click="menu = ! menu"
                class="rounded-md p-2 text-slate-600 hover:bg-slate-100"
                aria-label="{{ __('Abrir menú') }}">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
            </svg>
        </button>
        <span class="truncate font-semibold text-slate-800">{{ config('app.name') }}</span>
    </div>

    <div class="flex min-h-screen">

        {{-- Menú lateral --}}
        <aside x-cloak
               :class="menu ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 transform flex-col bg-slate-900 text-slate-200 transition-transform duration-200 lg:static lg:translate-x-0">

            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-white/10 px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">
                    ST
                </span>
                <span class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold text-white">Cumplimiento STPS</span>
                    <span class="text-xs text-slate-400">Panel de control</span>
                </span>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                @foreach ($modulos as $clave => $modulo)
                    @can($modulo['permiso'])
                        <div>
                            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                {{ $modulo['nombre'] }}
                            </p>
                            <ul class="mt-1.5 space-y-0.5">
                                @foreach ($modulo['items'] as $item)
                                    @php
                                        $disponible = $item['implementado'] && $item['ruta'] !== null && Route::has($item['ruta']);
                                    @endphp
                                    <li>
                                        @if ($disponible)
                                            <a href="{{ route($item['ruta']) }}"
                                               @class([
                                                   'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm transition',
                                                   'bg-indigo-500 text-white font-medium' => $rutaActual === $item['ruta'],
                                                   'text-slate-300 hover:bg-white/5 hover:text-white' => $rutaActual !== $item['ruta'],
                                               ])>
                                                {{ $item['nombre'] }}
                                            </a>
                                        @else
                                            <span class="flex items-center justify-between gap-2 rounded-md px-3 py-1.5 text-sm text-slate-500">
                                                <span>{{ $item['nombre'] }}</span>
                                                <span class="rounded bg-white/5 px-1.5 py-0.5 text-[10px] uppercase tracking-wide text-slate-500">
                                                    Pronto
                                                </span>
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endcan
                @endforeach
            </nav>
        </aside>

        {{-- Fondo para móvil --}}
        <div x-cloak x-show="menu" @click="menu = false"
             class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"></div>

        {{-- Contenido --}}
        <div class="flex min-w-0 flex-1 flex-col">

            <header class="hidden h-16 shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 lg:flex">
                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Centro de trabajo activo</p>
                    @if ($centroActivo !== null)
                        <p class="truncate text-sm font-semibold text-slate-800">
                            {{ $centroActivo->nombre }}
                            <span class="font-normal text-slate-500">· {{ $centroActivo->clave }}</span>
                        </p>
                    @else
                        <p class="text-sm text-slate-500">Sin centro de trabajo asignado</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    @if ($centrosDisponibles->count() > 1)
                        <form method="POST" action="{{ route('centro-trabajo-activo.update') }}">
                            @csrf
                            <label for="centro_trabajo_id" class="sr-only">{{ __('Cambiar centro de trabajo') }}</label>
                            <select id="centro_trabajo_id" name="centro_trabajo_id"
                                    onchange="this.form.submit()"
                                    class="rounded-md border-slate-300 py-1.5 pl-3 pr-8 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($centrosDisponibles as $centro)
                                    <option value="{{ $centro->id }}" @selected($centroActivo?->id === $centro->id)>
                                        {{ $centro->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif

                    <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700">
                            {{ mb_strtoupper(mb_substr(auth()->user()->nombre, 0, 1) . mb_substr(auth()->user()->apellido_paterno, 0, 1)) }}
                        </span>
                        <span class="hidden flex-col leading-tight xl:flex">
                            <span class="text-sm font-medium text-slate-800">{{ auth()->user()->nombre_completo }}</span>
                            <span class="text-xs text-slate-500">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-md px-2.5 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                                {{ __('Salir') }}
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
