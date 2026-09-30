<?php

namespace App\Http\Controllers\Audit;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\DiagnosticoIntegral;
use App\Models\Inspeccion;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    public function storeInspection(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('audit-inspection.write');

        $centro = $context->activo();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:seguridad,salud,maquinaria'],
            'fecha' => ['nullable', 'date'],
            'inspector_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        Inspeccion::create($data);

        return response('inspection.created', 201);
    }

    public function storeAudit(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('audit-inspection.write');

        $centro = $context->activo();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:sst,legal,integral'],
            'fecha' => ['nullable', 'date'],
            'auditor_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        Auditoria::create($data);

        return response('audit.created', 201);
    }

    public function storeDiagnosis(Request $request, CentroTrabajoContext $context): Response
    {
        abort_if($request->user()->hasRole('Auditor'), 403);

        Gate::authorize('audit-inspection.write');

        $centro = $context->activo();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'area' => ['nullable', 'string', 'max:255'],
            'fecha' => ['nullable', 'date'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        DiagnosticoIntegral::create($data);

        return response('diagnosis.created', 201);
    }
}
