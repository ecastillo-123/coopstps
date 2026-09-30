<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Diagnóstico integral')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Diagnóstico integral</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $diagnosticos = $centro !== null
            ? \App\Models\DiagnosticoIntegral::query()
                ->where('centro_trabajo_id', $centro->getKey())
                ->orderByDesc('fecha')
                ->get()
            : collect();
    @endphp

    @can('audit-inspection.write')
        @unless (auth()->user()->hasRole('Auditor'))
            <form method="POST" action="{{ route('audit.diagnosis.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <input name="titulo" placeholder="Título" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <textarea name="descripcion" placeholder="Descripción" class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2"></textarea>
                    <input name="area" placeholder="Área" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input name="fecha" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar diagnóstico</button>
            </form>
        @endunless
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Título</th>
                    <th class="px-4 py-2">Área</th>
                    <th class="px-4 py-2">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($diagnosticos as $diagnostico)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $diagnostico->titulo }}</td>
                        <td class="px-4 py-2">{{ $diagnostico->area ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $diagnostico->fecha?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No hay diagnósticos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
