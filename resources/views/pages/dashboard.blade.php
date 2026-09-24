<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Tablero')] class extends Component
{
    //
};
?>

@php
    $contexto = app(CentroTrabajoContext::class);
    $centroActivo = $contexto->activo();

    // Catálogo de KPIs con metas STPS (sección 12). En la Fase 7 se calculan
    // a partir de los datos operativos; por ahora se muestran sin valor.
    $kpis = [
        ['nombre' => 'Colaboradores capacitados', 'meta' => '100%'],
        ['nombre' => 'Cursos impartidos conforme al programa', 'meta' => '100%'],
        ['nombre' => 'Accidentes eléctricos registrados', 'meta' => '0%'],
        ['nombre' => 'Incidentes reportados con seguimiento', 'meta' => '100%'],
        ['nombre' => 'Inspecciones realizadas a tiempo', 'meta' => '≥ 95%'],
        ['nombre' => 'Mantenimiento programado cumplido', 'meta' => '≥ 95%'],
    ];

    $nucleo = [
        ['nombre' => 'Indicadores', 'detalle' => 'KPIs de cumplimiento global'],
        ['nombre' => 'Alertas', 'detalle' => 'Avisos de vencimiento y mantenimiento'],
        ['nombre' => 'Reportes', 'detalle' => 'Entregables ejecutivos en PDF'],
    ];

    $satelites = [
        ['nombre' => 'Seguridad y Salud', 'detalle' => 'NOM-017, NOM-036, NOM-035'],
        ['nombre' => 'Capacitación', 'detalle' => 'Cursos, bitácoras y evaluaciones'],
        ['nombre' => 'Mantenimiento', 'detalle' => 'NOM-004 y revisiones'],
        ['nombre' => 'Auditorías STPS', 'detalle' => 'Inspecciones e informes'],
        ['nombre' => 'Simulacros', 'detalle' => 'NOM-002 y NOM-033'],
    ];
@endphp

<div class="space-y-8">

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                Bienvenido, {{ auth()->user()->nombre }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                {{ now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
        </div>

        @if ($centroActivo !== null)
            <div class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Centro activo</p>
                <p class="font-semibold text-slate-800">{{ $centroActivo->nombre }}</p>
                <p class="text-xs text-slate-500">
                    R.P. {{ $centroActivo->numero_registro_patronal ?? 'sin registro' }}
                    @if ($centroActivo->estado)
                        · {{ $centroActivo->estado }}
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- KPIs --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Indicadores de cumplimiento</h2>
            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                Pendiente de datos · Fase 7
            </span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($kpis as $kpi)
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-600">{{ $kpi['nombre'] }}</p>
                    <div class="mt-3 flex items-end justify-between">
                        <span class="text-3xl font-semibold text-slate-300">—</span>
                        <span class="text-xs font-medium text-slate-500">Meta {{ $kpi['meta'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Núcleo --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Módulo central de cumplimiento</h2>
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($nucleo as $item)
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-5">
                    <p class="font-semibold text-indigo-900">{{ $item['nombre'] }}</p>
                    <p class="mt-1 text-sm text-indigo-700/80">{{ $item['detalle'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Módulos satélite --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Módulos</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($satelites as $item)
                <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $item['nombre'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $item['detalle'] }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                        Pronto
                    </span>
                </div>
            @endforeach
        </div>
    </section>
</div>
