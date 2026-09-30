<?php

use App\Models\CategoriaRelacionLaboral;
use App\Models\Puesto;
use App\Support\CentroTrabajoContext;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Trabajadores')] class extends Component {
    public function boot(): void
    {
        Gate::authorize('personal.modify');
    }
};
?>

<div class="space-y-6">
    <h1 class="text-2xl font-semibold text-slate-900">Trabajadores</h1>

    @php
        $centro = app(CentroTrabajoContext::class)->activo();
        $estado = request()->query('estado');
        $fechaDesde = request()->query('fecha_desde');
        $fechaHasta = request()->query('fecha_hasta');
        $puestoId = request()->query('puesto_id');
        $puestos = Puesto::where('activo', true)->orderBy('nombre')->get();
        $categorias = CategoriaRelacionLaboral::where('activo', true)->orderBy('nombre')->get();
        $trabajadores = collect();

        if ($centro !== null) {
            $consultaTrabajadores = $centro->trabajadores()
                ->with([
                    'puesto',
                    'cambiosCicloLaboral' => fn ($query) => $query->orderBy('fecha_evento')->orderBy('id'),
                    'cambiosCicloLaboral.categoriaRelacionLaboral',
                    'cambiosCicloLaboral.evidencias',
                ])
                ->orderBy('nombre');

            if ($estado === 'vigente') {
                $consultaTrabajadores->where('activo', true);
            } elseif ($estado === 'baja') {
                $consultaTrabajadores->where('activo', false);
            }

            if (is_string($puestoId) && ctype_digit($puestoId)) {
                $consultaTrabajadores->where('puesto_id', (int) $puestoId);
            }

            if ((is_string($fechaDesde) && $fechaDesde !== '') || (is_string($fechaHasta) && $fechaHasta !== '')) {
                $consultaTrabajadores->whereHas('cambiosCicloLaboral', function ($query) use ($fechaDesde, $fechaHasta): void {
                    if (is_string($fechaDesde) && $fechaDesde !== '') {
                        $query->whereDate('fecha_evento', '>=', $fechaDesde);
                    }

                    if (is_string($fechaHasta) && $fechaHasta !== '') {
                        $query->whereDate('fecha_evento', '<=', $fechaHasta);
                    }
                });
            }

            $trabajadores = $consultaTrabajadores->get();
        }
    @endphp

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
            <p class="font-semibold">No se pudo registrar el cambio laboral.</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                    @foreach ($puestos as $puesto)
                        <option value="{{ $puesto->id }}">{{ $puesto->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar trabajador</button>
        </form>
    @endcan

    <form method="GET" action="{{ route('workforce.workers') }}" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <label class="space-y-1 text-sm font-medium text-slate-700">
            <span>Estado</span>
            <select name="estado" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                <option value="">Todos</option>
                <option value="vigente" @selected($estado === 'vigente')>Vigente</option>
                <option value="baja" @selected($estado === 'baja')>Egreso / Baja</option>
            </select>
        </label>
        <label class="space-y-1 text-sm font-medium text-slate-700">
            <span>Desde el cambio</span>
            <input name="fecha_desde" type="date" value="{{ is_string($fechaDesde) ? $fechaDesde : '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
        </label>
        <label class="space-y-1 text-sm font-medium text-slate-700">
            <span>Hasta el cambio</span>
            <input name="fecha_hasta" type="date" value="{{ is_string($fechaHasta) ? $fechaHasta : '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
        </label>
        <label class="space-y-1 text-sm font-medium text-slate-700">
            <span>Puesto</span>
            <select name="puesto_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                <option value="">Todos</option>
                @foreach ($puestos as $puesto)
                    <option value="{{ $puesto->id }}" @selected((string) $puestoId === (string) $puesto->id)>{{ $puesto->nombre }}</option>
                @endforeach
            </select>
        </label>
        <div class="flex items-end gap-2">
            <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Buscar</button>
            <a href="{{ route('workforce.workers') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Limpiar</a>
        </div>
    </form>

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
                    <th class="px-4 py-2">Estado y ciclo laboral</th>
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
                        <td class="px-4 py-2">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $trabajador->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                {{ $trabajador->activo ? 'Vigente' : 'Egreso / Baja' }}
                            </span>
                            <details class="mt-2">
                                <summary class="cursor-pointer text-indigo-700">Historial y cambios</summary>
                                <div class="mt-3 min-w-80 space-y-4">
                                    <ol class="space-y-3 border-l border-slate-200 pl-4">
                                        @forelse ($trabajador->cambiosCicloLaboral as $cambio)
                                            <li>
                                                <p class="font-semibold text-slate-800">
                                                    {{ ucfirst($cambio->tipo === 'baja' ? 'egreso / baja' : $cambio->tipo) }}
                                                    <span class="font-normal text-slate-500">· {{ $cambio->fecha_evento->format('d/m/Y') }}</span>
                                                </p>
                                                @if ($cambio->categoriaRelacionLaboral !== null)
                                                    <p class="text-slate-600">Categoría: {{ $cambio->categoriaRelacionLaboral->nombre }}</p>
                                                @endif
                                                @can('personal.modify')
                                                    <ul class="mt-1 space-y-1">
                                                        @foreach ($cambio->evidencias as $evidencia)
                                                            <li>
                                                                <a class="text-indigo-700 underline" href="{{ route('workforce.workers.lifecycle-evidence.download', [$trabajador, $evidencia]) }}">{{ $evidencia->nombre_original }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endcan
                                            </li>
                                        @empty
                                            <li class="text-slate-500">Sin cambios laborales registrados.</li>
                                        @endforelse
                                    </ol>

                                    @can('personal.modify')
                                        <form method="POST" action="{{ route('workforce.workers.lifecycle.store', $trabajador) }}" enctype="multipart/form-data" class="space-y-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                            @csrf
                                            <h2 class="font-semibold text-slate-800">Registrar cambio laboral</h2>
                                            <label class="block space-y-1 text-sm font-medium text-slate-700">
                                                <span>Tipo de cambio</span>
                                                <select name="tipo" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                                                    <option value="">Seleccione</option>
                                                    <option value="ingreso">Ingreso</option>
                                                    <option value="reingreso">Reingreso</option>
                                                    <option value="baja">Egreso / Baja</option>
                                                </select>
                                            </label>
                                            <label class="block space-y-1 text-sm font-medium text-slate-700">
                                                <span>Fecha del cambio</span>
                                                <input name="fecha_evento" type="date" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                                            </label>
                                            <label class="block space-y-1 text-sm font-medium text-slate-700">
                                                <span>Categoría de relación laboral para ingreso o reingreso</span>
                                                <select name="categoria_relacion_laboral_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                                                    <option value="">No aplica</option>
                                                    @foreach ($categorias as $categoria)
                                                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <label class="block space-y-1 text-sm font-medium text-slate-700">
                                                <span>Evidencia (PDF, JPG o PNG; máximo 5 MB por archivo)</span>
                                                <input name="evidencias[]" type="file" accept=".pdf,.jpg,.jpeg,.png" multiple required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-normal">
                                            </label>
                                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Guardar cambio</button>
                                        </form>
                                    @endcan
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">No hay trabajadores para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
