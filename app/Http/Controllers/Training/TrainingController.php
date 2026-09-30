<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\Capacitacion;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TrainingController extends Controller
{
    public function storeCourse(Request $request, CentroTrabajoContext $context): Response
    {
        Gate::authorize('training.register');

        $centro = $context->activo();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'in:seguridad,salud,induccion,capacitacion'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'instructor' => ['nullable', 'string', 'max:255'],
            'duracion_horas' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['centro_trabajo_id'] = $centro?->getKey();

        Capacitacion::create($data);

        return response('course.created', 201);
    }
}
