<?php

use App\Support\CentroTrabajoContext;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Cursos de capacitación')] class extends Component {
    //
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Cursos de capacitación</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $cursos = $centro !== null
            ? \App\Models\Capacitacion::query()
                ->where('centro_trabajo_id', $centro->getKey())
                ->orderByDesc('fecha_inicio')
                ->get()
            : collect();
    @endphp

    @can('training.register')
        <form method="POST" action="{{ route('training.courses.store') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            @csrf
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <input name="nombre" placeholder="Nombre del curso" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <textarea name="descripcion" placeholder="Descripción" class="rounded-lg border border-slate-300 px-3 py-2 text-sm sm:col-span-2"></textarea>
                <select name="tipo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Tipo</option>
                    <option value="seguridad">Seguridad</option>
                    <option value="salud">Salud</option>
                    <option value="induccion">Inducción</option>
                    <option value="capacitacion">Capacitación</option>
                </select>
                <input name="fecha_inicio" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="fecha_fin" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="instructor" placeholder="Instructor" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="duracion_horas" type="number" min="1" placeholder="Duración (horas)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar curso</button>
        </form>
    @endcan

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Tipo</th>
                    <th class="px-4 py-2">Inicio</th>
                    <th class="px-4 py-2">Fin</th>
                    <th class="px-4 py-2">Instructor</th>
                    <th class="px-4 py-2">Duración</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cursos as $curso)
                    <tr>
                        <td class="px-4 py-2 font-medium">{{ $curso->nombre }}</td>
                        <td class="px-4 py-2">{{ $curso->tipo ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $curso->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $curso->fecha_fin?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $curso->instructor ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $curso->duracion_horas ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">No hay cursos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
