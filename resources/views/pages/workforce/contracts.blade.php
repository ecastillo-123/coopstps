<?php

use App\Models\CategoriaRelacionLaboral;
use App\Models\ModalidadContrato;
use App\Models\PlantillaContrato;
use App\Support\CentroTrabajoContext;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Contratos del personal')] class extends Component {
    public function boot(): void
    {
        Gate::authorize('personal.modify');
    }
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Contratos del personal</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $contratos = $centro !== null
            ? \App\Models\ContratoTrabajador::query()
                ->whereHas('trabajador', fn ($q) => $q->where('centro_trabajo_id', $centro->getKey()))
                ->with(['trabajador', 'plantillaContrato', 'modalidadContrato', 'categoriaRelacionLaboral'])
                ->orderByDesc('fecha_inicio')
                ->get()
            : collect();
    @endphp

    @can('personal.modify')
        <form method="POST" action="{{ route('workforce.contracts.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <select name="trabajador_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Trabajador</option>
                    @if ($centro !== null)
                        @foreach ($centro->trabajadores()->orderBy('nombre')->get() as $trabajador)
                            <option value="{{ $trabajador->id }}">{{ $trabajador->nombre }} {{ $trabajador->apellido_paterno }}</option>
                        @endforeach
                    @endif
                </select>
                <select name="plantilla_contrato_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Plantilla</option>
                    @foreach (PlantillaContrato::where('activo', true)->orderBy('nombre')->get() as $plantilla)
                        <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                    @endforeach
                </select>
                <select name="modalidad_contrato_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Modalidad</option>
                    @foreach (ModalidadContrato::where('activo', true)->orderBy('nombre')->get() as $modalidad)
                        <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                    @endforeach
                </select>
                <select name="categoria_relacion_laboral_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Categoría</option>
                    @foreach (CategoriaRelacionLaboral::where('activo', true)->orderBy('nombre')->get() as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
                <input name="fecha_inicio" type="date" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="fecha_fin" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="salario" type="number" step="0.01" min="0" placeholder="Salario" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar contrato</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Trabajador</th>
                    <th class="px-4 py-2">Plantilla</th>
                    <th class="px-4 py-2">Modalidad</th>
                    <th class="px-4 py-2">Categoría</th>
                    <th class="px-4 py-2">Inicio</th>
                    <th class="px-4 py-2">Salario</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($contratos as $contrato)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $contrato->trabajador->nombre }} {{ $contrato->trabajador->apellido_paterno }}</td>
                        <td class="px-4 py-2">{{ $contrato->plantillaContrato->nombre }}</td>
                        <td class="px-4 py-2">{{ $contrato->modalidadContrato->nombre }}</td>
                        <td class="px-4 py-2">{{ $contrato->categoriaRelacionLaboral->nombre }}</td>
                        <td class="px-4 py-2">{{ $contrato->fecha_inicio?->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $contrato->salario !== null ? '$'.number_format($contrato->salario, 2) : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No hay contratos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
