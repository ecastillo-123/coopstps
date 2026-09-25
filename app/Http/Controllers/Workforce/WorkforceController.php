<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Models\ContratoTrabajador;
use App\Models\Trabajador;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class WorkforceController extends Controller
{
    public function storeWorker(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('personal.register');

        $centro = $context->activo();

        $data = $request->validate([
            'numero_empleado' => ['nullable', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'curp' => ['nullable', 'string', 'max:18'],
            'nss' => ['nullable', 'string', 'max:20'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_ingreso' => ['required', 'date'],
            'puesto_id' => ['nullable', 'exists:puestos,id'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        Trabajador::create($data);

        return response('worker.created', 201);
    }

    public function storeContract(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('personal.modify');

        $centro = $context->activo();

        $data = $request->validate([
            'trabajador_id' => ['required', 'integer'],
            'plantilla_contrato_id' => ['required', 'exists:plantillas_contrato,id'],
            'modalidad_contrato_id' => ['required', 'exists:modalidades_contrato,id'],
            'categoria_relacion_laboral_id' => ['required', 'exists:categorias_relacion_laboral,id'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'salario' => ['nullable', 'numeric', 'min:0'],
        ]);

        $trabajador = Trabajador::query()
            ->where('centro_trabajo_id', $centro?->getKey())
            ->find($data['trabajador_id']);

        abort_if($trabajador === null, 404);

        ContratoTrabajador::create($data);

        return response('contract.created', 201);
    }
}
