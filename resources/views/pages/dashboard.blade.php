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
        ['nombre' => 'Normas y obligaciones', 'detalle' => 'Comisiones, NOMs y programas anuales'],
        ['nombre' => 'Simulacros', 'detalle' => 'Programación NOM-002 y NOM-033'],
    ];

    $workflows = [
        ['nombre' => 'Trabajadores', 'ruta' => 'workforce.workers', 'grupo' => 'Colaboradores'],
        ['nombre' => 'Contratos', 'ruta' => 'workforce.contracts', 'grupo' => 'Colaboradores'],
        ['nombre' => 'Hallazgos', 'ruta' => 'sst.findings', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Acciones correctivas', 'ruta' => 'sst.actions', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Comisiones SST', 'ruta' => 'sst.commissions', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Mantenimiento', 'ruta' => 'sst.maintenance', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Capacitación', 'ruta' => 'training.courses', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Inspecciones', 'ruta' => 'audit.inspections', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Auditorías', 'ruta' => 'audit.audits', 'grupo' => 'Seguridad y Salud'],
        ['nombre' => 'Diagnóstico integral', 'ruta' => 'audit.diagnosis', 'grupo' => 'Seguridad y Salud'],
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

    {{-- Núcleo central de cumplimiento --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Módulo central de cumplimiento</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($nucleo as $item)
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 p-5">
                    <p class="font-semibold text-indigo-900">{{ $item['nombre'] }}</p>
                    <p class="mt-1 text-sm text-indigo-700/80">{{ $item['detalle'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

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

    {{-- Workflows implementados --}}
    <section>
        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Workflows implementados</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($workflows as $workflow)
                <a href="{{ route($workflow['ruta']) }}"
                   class="group flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-200 hover:shadow-md">
                    <div>
                        <p class="font-semibold text-slate-800 group-hover:text-indigo-700">{{ $workflow['nombre'] }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $workflow['grupo'] }}</p>
                    </div>
                    <svg class="h-5 w-5 shrink-0 text-slate-400 transition group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            @endforeach
        </div>
    </section>
</div>
