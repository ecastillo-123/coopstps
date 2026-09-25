<?php

use App\Models\Puesto;
use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Trabajadores')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Trabajadores</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $trabajadores = $centro !== null
            ? $centro->trabajadores()->with('puesto')->orderBy('nombre')->get()
            : collect();
    @endphp

    @can('personal.register')
        <form method="POST" action="{{ route('workforce.workers.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="numero_empleado" placeholder="Número de empleado" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="nombre" placeholder="Nombre" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="apellido_paterno" placeholder="Apellido paterno" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="apellido_materno" placeholder="Apellido materno" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="curp" placeholder="CURP" maxlength="18" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="nss" placeholder="NSS" maxlength="20" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="rfc" placeholder="RFC" maxlength="13" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="fecha_nacimiento" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="fecha_ingreso" type="date" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="puesto_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Sin puesto</option>
                    @foreach (Puesto::where('activo', true)->orderBy('nombre')->get() as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar trabajador</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Número</th>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">CURP</th>
                    <th class="px-4 py-2">NSS</th>
                    <th class="px-4 py-2">Puesto</th>
                    <th class="px-4 py-2">Ingreso</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($trabajadores as $trabajador)
                    <tr>
                        <td class="px-4 py-2">{{ $trabajador->numero_empleado ?? '—' }}</td>
                        <td class="px-4 py-2 font-medium">{{ $trabajador->nombre }} {{ $trabajador->apellido_paterno }} {{ $trabajador->apellido_materno }}</td>
                        <td class="px-4 py-2">{{ $trabajador->curp ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $trabajador->nss ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $trabajador->puesto?->nombre ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $trabajador->fecha_ingreso?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No hay trabajadores registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
