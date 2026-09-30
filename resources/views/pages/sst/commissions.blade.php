<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Comisiones SST')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Comisiones SST</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $comisiones = $centro !== null
            ? \App\Models\ComisionSst::query()
                ->where('centro_trabajo_id', $centro->getKey())
                ->with('lider')
                ->orderByDesc('vigencia_inicio')
                ->get()
            : collect();
    @endphp

    @can('nom035.register')
        <form method="POST" action="{{ route('sst.commissions.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="nombre" placeholder="Nombre de la comisión" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="vigencia_inicio" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="vigencia_fin" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar comisión</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Vigencia inicio</th>
                    <th class="px-4 py-2">Vigencia fin</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($comisiones as $comision)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $comision->nombre }}</td>
                        <td class="px-4 py-2">{{ $comision->vigencia_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $comision->vigencia_fin?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">No hay comisiones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
