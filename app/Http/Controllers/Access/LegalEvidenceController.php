<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\LegalEvidence;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LegalEvidenceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Administrador'), 403);

        $validated = $request->validate([
            'topic' => ['bail', 'required', 'string', Rule::in(['legal_nom_activation', 'indefinite_retention'])],
            'reference' => [
                'bail',
                'required',
                'string',
                'max:1000',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (trim($value) === '') {
                        $fail('La referencia legal es obligatoria.');
                    }
                },
            ],
            'evidence_url' => [
                'bail',
                'required',
                'url',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $parts = parse_url($value);
                    $parts = is_array($parts) ? $parts : [];
                    $host = strtolower($parts['host'] ?? '');
                    $path = $parts['path'] ?? '';
                    $isOfficialHost = $host === 'dof.gob.mx' || str_ends_with($host, '.dof.gob.mx');

                    if (($parts['scheme'] ?? null) !== 'https' || ! $isOfficialHost || $path === '' || $path === '/') {
                        $fail('La URL debe pertenecer al DOF oficial, usar HTTPS e incluir una ruta.');
                    }
                },
            ],
            'effective_at' => ['required', 'date_format:Y-m-d'],
        ], [
            'topic.required' => 'Selecciona el fundamento legal.',
            'topic.in' => 'El fundamento legal seleccionado no es válido.',
            'reference.required' => 'La referencia legal es obligatoria.',
            'reference.string' => 'La referencia legal debe ser texto.',
            'reference.max' => 'La referencia legal no puede exceder 1000 caracteres.',
            'evidence_url.required' => 'La URL oficial del DOF es obligatoria.',
            'evidence_url.url' => 'La URL del DOF debe ser válida.',
            'evidence_url.max' => 'La URL del DOF no puede exceder 2048 caracteres.',
            'effective_at.required' => 'La fecha de vigencia es obligatoria.',
            'effective_at.date_format' => 'La fecha de vigencia debe tener el formato AAAA-MM-DD.',
        ]);

        LegalEvidence::query()->create([
            ...$validated,
            'reference' => trim($validated['reference']),
            'verified_by_user_id' => $request->user()->getKey(),
            'approved_by_user_id' => $request->user()->getKey(),
            'approved' => true,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.legal-evidence.index')
            ->with('status', 'La evidencia fue registrada y aprobada.');
    }

    public function revoke(Request $request, LegalEvidence $legalEvidence): RedirectResponse
    {
        abort_unless($request->user()?->hasRole('Administrador'), 403);
        abort_unless($legalEvidence->approved && $legalEvidence->revoked_at === null, 409);

        $validated = $request->validate([
            'reason' => ['bail', 'required', 'string', 'min:10', 'max:1000'],
        ], [
            'reason.required' => 'Indica el motivo de la revocación.',
            'reason.string' => 'El motivo debe ser texto.',
            'reason.min' => 'El motivo debe contener al menos 10 caracteres.',
            'reason.max' => 'El motivo no puede exceder 1000 caracteres.',
        ]);

        $legalEvidence->approved = false;
        $legalEvidence->revoked_by_user_id = $request->user()->getKey();
        $legalEvidence->revoked_at = now();
        $legalEvidence->revocation_reason = trim($validated['reason']);
        $legalEvidence->save();

        return redirect()
            ->route('admin.legal-evidence.index')
            ->with('status', 'La aprobación fue revocada.');
    }
}
