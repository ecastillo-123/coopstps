<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Hallazgos SST')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Hallazgos SST</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $hallazgos = $centro !== null
            ? \App\Models\Hallazgo::query()
                ->where('centro_trabajo_id', $centro->getKey())
                ->orderByDesc('detected_at')
                ->get()
            : collect();
    @endphp

    @can('nom035.register')
        <form method="POST" action="{{ route('sst.findings.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="titulo" placeholder="Título" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="descripcion" placeholder="Descripción" class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2"></textarea>
                <select name="riesgo" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Riesgo</option>
                    <option value="bajo">Bajo</option>
                    <option value="medio">Medio</option>
                    <option value="alto">Alto</option>
                    <option value="critico">Crítico</option>
                </select>
                <select name="estado" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="abierto">Abierto</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="cerrado">Cerrado</option>
                </select>
                <select name="tipo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Tipo</option>
                    <option value="seguridad">Seguridad</option>
                    <option value="salud">Salud</option>
                    <option value="nom">NOM</option>
                </select>
                <input name="referencia_nom" placeholder="Referencia NOM" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="detected_at" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar hallazgo</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Título</th>
                    <th class="px-4 py-2">Riesgo</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">NOM</th>
                    <th class="px-4 py-2">Detectado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($hallazgos as $hallazgo)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $hallazgo->titulo }}</td>
                        <td class="px-4 py-2">{{ $hallazgo->riesgo }}</td>
                        <td class="px-4 py-2">{{ $hallazgo->estado }}</td>
                        <td class="px-4 py-2">{{ $hallazgo->tipo ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $hallazgo->referencia_nom ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $hallazgo->detected_at?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No hay hallazgos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
