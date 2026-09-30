<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Acciones correctivas SST')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Acciones correctivas SST</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $acciones = $centro !== null
            ? \App\Models\AccionCorrectiva::query()
                ->whereHas('hallazgo', fn ($q) => $q->where('centro_trabajo_id', $centro->getKey()))
                ->with(['hallazgo', 'responsable'])
                ->orderByDesc('created_at')
                ->get()
            : collect();
    @endphp

    @can('nom035.modify')
        <form method="POST" action="{{ route('sst.actions.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <select name="hallazgo_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Hallazgo</option>
                    @if ($centro !== null)
                        @foreach ($centro->hallazgos()->orderBy('titulo')->get() as $h)
                            <option value="{{ $h->id }}">{{ $h->titulo }}</option>
                        @endforeach
                    @endif
                </select>
                <textarea name="descripcion" placeholder="Descripción" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2"></textarea>
                <input name="fecha_compromiso" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="estado" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="pendiente">Pendiente</option>
                    <option value="en_progreso">En progreso</option>
                    <option value="completada">Completada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar acción</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Hallazgo</th>
                    <th class="px-4 py-2">Descripción</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Compromiso</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($acciones as $accion)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $accion->hallazgo->titulo }}</td>
                        <td class="px-4 py-2">{{ $accion->descripcion }}</td>
                        <td class="px-4 py-2">{{ $accion->estado }}</td>
                        <td class="px-4 py-2">{{ $accion->fecha_compromiso?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">No hay acciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
