<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Mantenimiento SST')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Mantenimiento SST</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $mantenimientos = $centro !== null
            ? \App\Models\MantenimientoSst::query()
                ->where('centro_trabajo_id', $centro->getKey())
                ->orderByDesc('proxima_fecha')
                ->get()
            : collect();
    @endphp

    @can('nom035.register')
        <form method="POST" action="{{ route('sst.maintenance.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="equipo" placeholder="Equipo / activo" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="tipo" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="preventivo">Preventivo</option>
                    <option value="correctivo">Correctivo</option>
                </select>
                <input name="frecuencia" placeholder="Frecuencia" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="ultima_fecha" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="proxima_fecha" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar mantenimiento</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Equipo</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Frecuencia</th>
                    <th class="px-4 py-2">Última fecha</th>
                    <th class="px-4 py-2">Próxima fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($mantenimientos as $mantenimiento)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $mantenimiento->equipo }}</td>
                        <td class="px-4 py-2">{{ $mantenimiento->tipo }}</td>
                        <td class="px-4 py-2">{{ $mantenimiento->frecuencia ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $mantenimiento->ultima_fecha?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $mantenimiento->proxima_fecha?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">No hay mantenimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
