@php
    use App\Support\CentroTrabajoContext;
    use Illuminate\Support\Facades\Route;

    $contexto = app(CentroTrabajoContext::class);
    $centroActivo = $contexto->activo();
    $centrosDisponibles = $contexto->disponibles();

    $modulos = collect(config('sistema.modulos'));
    $modulosSuperiores = $modulos->filter(fn ($m) => ($m['ubicacion'] ?? 'lateral') === 'superior');
    $modulosLaterales = $modulos->filter(fn ($m) => ($m['ubicacion'] ?? 'lateral') !== 'superior');

    $rutaActual = request()->route()?->getName();
    $moduloEstaActivo = fn (array $modulo): bool => collect($modulo['items'])
        ->contains(fn ($item) => $item['ruta'] !== null && $item['ruta'] === $rutaActual);

    // Clases literales para que Tailwind las detecte al compilar.
    $paletaSuperior = [
        'blue' => ['bg-blue-600 hover:bg-blue-500', 'text-blue-700 border-blue-200'],
        'indigo' => ['bg-indigo-600 hover:bg-indigo-500', 'text-indigo-700 border-indigo-200'],
        'emerald' => ['bg-emerald-600 hover:bg-emerald-500', 'text-emerald-700 border-emerald-200'],
        'amber' => ['bg-amber-500 hover:bg-amber-400', 'text-amber-700 border-amber-200'],
    ];
    $paletaDe = fn (array $modulo): array => $paletaSuperior[$modulo['color'] ?? 'blue'] ?? $paletaSuperior['blue'];

    $paletaLateral = [
        'amber' => [
            'header' => 'text-amber-900/70',
            'item-active' => 'bg-amber-500 text-white shadow-sm',
            'item-inactive' => 'text-amber-900 hover:bg-amber-100 hover:text-amber-950',
            'badge' => 'bg-amber-100 text-amber-700',
        ],
        'sky' => [
            'header' => 'text-sky-900/70',
            'item-active' => 'bg-sky-600 text-white shadow-sm',
            'item-inactive' => 'text-slate-700 hover:bg-white hover:text-sky-800',
            'badge' => 'bg-sky-100 text-sky-700',
        ],
    ];
    $clasesLateral = fn (array $modulo): array => $paletaLateral[$modulo['bloque'] ?? 'sky'] ?? $paletaLateral['sky'];
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
<div x-data="{ lateral: false }" class="flex h-screen flex-col overflow-hidden">

    {{-- Barra superior --}}
    <header class="relative z-30 shrink-0 border-b border-slate-800 bg-slate-900 text-slate-200">

        <div class="flex h-14 items-center gap-3 px-4">
            <button type="button"
                    x-on:click="lateral = ! lateral"
                    class="rounded-md p-2 text-slate-300 hover:bg-white/10 lg:hidden"
                    aria-label="{{ __('Abrir menú lateral') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-xs font-bold text-white">
                    ST
                </span>
                <span class="hidden text-sm font-semibold text-white sm:inline">Cumplimiento STPS</span>
            </a>

            <div class="ml-auto flex shrink-0 items-center gap-2">
                <span class="hidden flex-col items-end leading-tight xl:flex">
                    <span class="text-sm font-medium text-white">{{ auth()->user()->nombre_completo }}</span>
                    <span class="text-xs text-slate-400">{{ auth()->user()->getRoleNames()->first() }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-md px-2.5 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white">
                        {{ __('Salir') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Navegación principal: solo el nombre del módulo, submenús al hacer clic --}}
        <nav data-nav="superior" class="flex flex-wrap items-center gap-1.5 px-3 pb-2.5">
            @foreach ($modulosSuperiores as $clave => $modulo)
                @can($modulo['permiso'])
                    @php
                        $activo = $moduloEstaActivo($modulo);
                        [$colorBoton, $colorAcento] = $paletaDe($modulo);
                    @endphp
                    <div class="relative"
                         x-data="{ abierto: false }"
                         x-on:keydown.escape.window="abierto = false">
                        <button type="button"
                                x-on:click="abierto = ! abierto"
                                x-on:click.outside="abierto = false"
                                x-bind:aria-expanded="abierto ? 'true' : 'false'"
                                aria-haspopup="true"
                                @class([
                                    'flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-white shadow-sm transition lg:px-3 lg:text-xs',
                                    $colorBoton,
                                    'ring-2 ring-white/70 ring-offset-1 ring-offset-slate-900' => $activo,
                                ])>
                            {{ $modulo['nombre'] }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-150"
                                 x-bind:class="abierto ? 'rotate-180' : ''"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div x-show="abierto"
                             x-cloak
                             x-transition.origin.top.left
                             class="absolute left-0 z-50 mt-1 w-64 rounded-lg border border-slate-200 bg-white py-1.5 shadow-xl">
                            <p class="mb-1 border-b px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider {{ $colorAcento }}">
                                {{ $modulo['nombre'] }}
                            </p>
                            <ul>
                                @foreach ($modulo['items'] as $item)
                                    @php
                                        $disponible = $item['implementado'] && $item['ruta'] !== null && Route::has($item['ruta']);
                                        $visible = (! isset($item['permiso']) || auth()->user()->can($item['permiso']))
                                            && (! isset($item['rol']) || auth()->user()->hasRole($item['rol']));
                                    @endphp
                                    @if ($visible)
                                        <li>
                                            @if ($disponible)
                                                <a href="{{ route($item['ruta']) }}"
                                                   class="block px-3 py-1.5 text-sm text-slate-700 transition hover:bg-slate-100">
                                                    {{ $item['nombre'] }}
                                                </a>
                                            @else
                                                <span class="flex items-center justify-between gap-2 px-3 py-1.5 text-sm text-slate-400">
                                                    <span>{{ $item['nombre'] }}</span>
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] uppercase tracking-wide">
                                                        Pronto
                                                    </span>
                                                </span>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endcan
            @endforeach
        </nav>
    </header>

    <div class="flex min-h-0 flex-1">

        {{-- Menú lateral: módulos secundarios --}}
        <aside x-cloak
               x-bind:class="lateral ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 flex w-72 shrink-0 -translate-x-full transform flex-col overflow-y-auto border-r border-sky-100 bg-sky-50 transition-transform duration-200 lg:static lg:z-0 lg:translate-x-0">

            <div class="flex h-14 shrink-0 items-center justify-between gap-2 border-b border-sky-100 px-4 lg:hidden">
                <span class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-xs font-bold text-white">
                        ST
                    </span>
                    <span class="text-sm font-semibold text-slate-800">Cumplimiento STPS</span>
                </span>
                <button type="button"
                        x-on:click="lateral = false"
                        class="rounded-md p-2 text-slate-500 hover:bg-slate-100"
                        aria-label="{{ __('Cerrar menú lateral') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav data-nav="lateral" class="flex-1 space-y-6 px-3 py-4">
                @foreach ($modulosLaterales as $clave => $modulo)
                    @can($modulo['permiso'])
                        @php
                            $clases = $clasesLateral($modulo);
                        @endphp
                        <div class="rounded-lg border border-slate-200/60 bg-white p-2 shadow-sm">
                            <p class="px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider {{ $clases['header'] }}">
                                {{ $modulo['nombre'] }}
                            </p>
                            <ul class="mt-1 space-y-0.5">
                                @foreach ($modulo['items'] as $item)
                                    @php
                                        $disponible = $item['implementado'] && $item['ruta'] !== null && Route::has($item['ruta']);
                                        $visible = (! isset($item['permiso']) || auth()->user()->can($item['permiso']))
                                            && (! isset($item['rol']) || auth()->user()->hasRole($item['rol']));
                                    @endphp
                                    @if ($visible)
                                        <li>
                                            @if ($disponible)
                                                <a href="{{ route($item['ruta']) }}"
                                                   @class([
                                                        'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm transition',
                                                        $clases['item-active'] => $rutaActual === $item['ruta'],
                                                        $clases['item-inactive'] => $rutaActual !== $item['ruta'],
                                                    ])>
                                                    {{ $item['nombre'] }}
                                                </a>
                                            @else
                                                <span class="flex items-center justify-between gap-2 rounded-md px-3 py-1.5 text-sm text-slate-400">
                                                    <span>{{ $item['nombre'] }}</span>
                                                    <span class="rounded {{ $clases['badge'] }} px-1.5 py-0.5 text-[10px] uppercase tracking-wide">
                                                        Pronto
                                                    </span>
                                                </span>
                                            @endif
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endcan
                @endforeach
            </nav>
        </aside>

        <div x-cloak x-show="lateral" x-on:click="lateral = false"
             class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">

            <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3 sm:px-6">
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
            </div>

            <main class="min-h-0 flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</div>
@livewireScripts
</body>
</html>
