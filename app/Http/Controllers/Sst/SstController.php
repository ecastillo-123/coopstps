<?php

namespace App\Http\Controllers\Sst;

use App\Http\Controllers\Controller;
use App\Models\AccionCorrectiva;
use App\Models\ComisionSst;
use App\Models\Hallazgo;
use App\Models\MantenimientoSst;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SstController extends Controller
{
    public function storeFinding(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('nom035.register');

        $centro = $context->activo();

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'riesgo' => ['required', 'string', 'in:bajo,medio,alto,critico'],
            'estado' => ['required', 'string', 'in:abierto,en_progreso,cerrado'],
            'tipo' => ['nullable', 'string', 'in:seguridad,salud,nom'],
            'referencia_nom' => ['nullable', 'string', 'max:50'],
            'detected_at' => ['nullable', 'date'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        Hallazgo::create($data);

        return response('finding.created', 201);
    }

    public function storeAction(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('nom035.modify');

        $centro = $context->activo();

        $data = $request->validate([
            'hallazgo_id' => ['required', 'integer'],
            'descripcion' => ['required', 'string'],
            'responsable_id' => ['nullable', 'exists:users,id'],
            'fecha_compromiso' => ['nullable', 'date'],
            'estado' => ['required', 'string', 'in:pendiente,en_progreso,completada,cancelada'],
            'evidencia_url' => ['nullable', 'string', 'max:500'],
        ]);

        $hallazgo = Hallazgo::query()
            ->where('centro_trabajo_id', $centro?->getKey())
            ->find($data['hallazgo_id']);

        abort_if($hallazgo === null, 404);

        AccionCorrectiva::create($data);

        return response('action.created', 201);
    }

    public function storeCommission(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('nom035.register');

        $centro = $context->activo();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'lider_id' => ['nullable', 'exists:users,id'],
            'vigencia_inicio' => ['nullable', 'date'],
            'vigencia_fin' => ['nullable', 'date', 'after_or_equal:vigencia_inicio'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        ComisionSst::create($data);

        return response('commission.created', 201);
    }

    public function storeMaintenance(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('nom035.register');

        $centro = $context->activo();

        $data = $request->validate([
            'equipo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:preventivo,correctivo'],
            'frecuencia' => ['nullable', 'string', 'max:50'],
            'ultima_fecha' => ['nullable', 'date'],
            'proxima_fecha' => ['nullable', 'date', 'after_or_equal:ultima_fecha'],
            'responsable_id' => ['nullable', 'exists:users,id'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        MantenimientoSst::create($data);

        return response('maintenance.created', 201);
    }
}
