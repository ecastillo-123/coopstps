<?php

use App\Models\LegalEvidence;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::app')] #[Title('Evidencia jurídica DOF')] class extends Component {
    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);
    }

    #[Computed]
    public function legalEvidences(): Collection
    {
        return LegalEvidence::query()
            ->with(['verifiedBy', 'approvedBy', 'revokedBy'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }
};
?>

<div class="flex flex-col gap-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Evidencia jurídica del DOF</h1>
        <p class="mt-1 text-sm text-slate-600">
            Registra, verifica y aprueba publicaciones oficiales. La aprobación permanece vigente hasta que se revoque expresamente.
        </p>
    </div>

    @if (session('status'))
        <div role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Registrar y aprobar evidencia</h2>
            <p class="mt-1 text-sm text-slate-500">
                La cuenta administradora que registra la evidencia también queda asentada como verificadora y aprobadora.
                Una fecha futura no habilita el fundamento antes de su entrada en vigor.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.legal-evidence.store') }}" class="flex flex-col gap-4">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="topic" class="mb-1 block text-sm font-medium text-slate-700">Fundamento legal</label>
                    <select id="topic" name="topic" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="legal_nom_activation" @selected(old('topic', 'legal_nom_activation') === 'legal_nom_activation')>Activación de reglas NOM</option>
                        <option value="indefinite_retention" @selected(old('topic') === 'indefinite_retention')>Retención indefinida</option>
                    </select>
                    @error('topic')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="effective_at" class="mb-1 block text-sm font-medium text-slate-700">Fecha de entrada en vigor</label>
                    <input id="effective_at" name="effective_at" type="date" value="{{ old('effective_at', today()->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('effective_at')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="reference" class="mb-1 block text-sm font-medium text-slate-700">Referencia legal</label>
                    <input id="reference" name="reference" type="text" value="{{ old('reference') }}" maxlength="1000" required placeholder="Número de publicación, artículo o disposición" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('reference')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="evidence_url" class="mb-1 block text-sm font-medium text-slate-700">URL oficial del DOF</label>
                    <input id="evidence_url" name="evidence_url" type="url" value="{{ old('evidence_url') }}" maxlength="2048" required placeholder="https://dof.gob.mx/..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('evidence_url')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Registrar y aprobar
            </button>
        </form>
    </section>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Evidencias registradas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Fundamento y fuente</th>
                        <th scope="col" class="px-4 py-3 font-medium">Verificación y aprobación</th>
                        <th scope="col" class="px-4 py-3 font-medium">Estado y vigencia</th>
                        <th scope="col" class="px-4 py-3 font-medium">Revocación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($this->legalEvidences as $evidence)
                        <tr class="align-top">
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1">
                                    <p class="font-medium text-slate-900">
                                        {{ $evidence->topic === 'legal_nom_activation' ? 'Activación de reglas NOM' : 'Retención indefinida' }}
                                    </p>
                                    <p class="text-slate-700">{{ $evidence->reference }}</p>
                                    @if ($evidence->evidence_url)
                                        <a href="{{ $evidence->evidence_url }}" target="_blank" rel="noopener noreferrer" class="break-all text-indigo-700 underline hover:text-indigo-900">
                                            Consultar publicación oficial
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-600">
                                <div class="flex flex-col gap-1">
                                    <p>Verificó: {{ $evidence->verifiedBy?->nombre_completo ?? '—' }}</p>
                                    <p>Aprobó: {{ $evidence->approvedBy?->nombre_completo ?? '—' }}</p>
                                    @if ($evidence->approved_at)
                                        <p>{{ $evidence->approved_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-2">
                                    @if ($evidence->revoked_at)
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">Revocada</span>
                                    @elseif ($evidence->approved)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aprobada</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Pendiente</span>
                                    @endif
                                    <p class="text-slate-600">Vigente desde: {{ $evidence->effective_at?->format('d/m/Y') ?? 'Sin fecha' }}</p>
                                </div>
                            </td>
                            <td class="min-w-64 px-4 py-4">
                                @if ($evidence->revoked_at)
                                    <div class="flex flex-col gap-1">
                                        <p class="font-medium text-red-800">{{ $evidence->revoked_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-slate-700">{{ $evidence->revocation_reason }}</p>
                                        <p class="text-xs text-slate-500">Revocó: {{ $evidence->revokedBy?->nombre_completo ?? '—' }}</p>
                                    </div>
                                @elseif ($evidence->approved)
                                    <form method="POST" action="{{ route('admin.legal-evidence.revoke', $evidence) }}" onsubmit="return confirm('¿Confirma que desea revocar esta aprobación?');" class="flex flex-col gap-2">
                                        @csrf
                                        <label for="reason-{{ $evidence->id }}" class="block text-xs font-medium text-slate-700">Motivo de revocación</label>
                                        <textarea id="reason-{{ $evidence->id }}" name="reason" rows="2" minlength="10" maxlength="1000" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('reason') }}</textarea>
                                        @error('reason')
                                            <p class="text-sm text-red-700">{{ $message }}</p>
                                        @enderror
                                        <button type="submit" class="rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                            Revocar aprobación
                                        </button>
                                    </form>
                                @else
                                    <p class="text-slate-500">No hay una aprobación activa para revocar.</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Todavía no hay evidencias jurídicas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
