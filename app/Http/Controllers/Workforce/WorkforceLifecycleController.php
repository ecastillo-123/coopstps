<?php

namespace App\Http\Controllers\Workforce;

use App\Http\Controllers\Controller;
use App\Models\EvidenciaCicloLaboral;
use App\Support\CentroTrabajoContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class WorkforceLifecycleController extends Controller
{
    public function store(Request $request, int $trabajador, CentroTrabajoContext $context): Response
    {
        Gate::authorize('personal.modify');

        $centro = $context->activo();
        abort_if($centro === null, 404);

        $worker = $centro->trabajadores()->findOrFail($trabajador);

        $data = $request->validate([
            'tipo' => ['required', Rule::in(['ingreso', 'reingreso', 'baja'])],
            'fecha_evento' => ['required', 'date', 'before_or_equal:today'],
            'categoria_relacion_laboral_id' => [
                'required_if:tipo,ingreso,reingreso',
                'nullable',
                'integer',
                'exists:categorias_relacion_laboral,id',
            ],
            'evidencias' => ['required', 'array', 'min:1', 'max:5'],
            'evidencias.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'extensions:pdf,jpg,jpeg,png', 'max:5120'],
        ], [
            'categoria_relacion_laboral_id.required_if' => 'Seleccione una categoría para registrar un ingreso o reingreso.',
        ]);

        $uploadedFiles = $data['evidencias'];
        $storedPaths = [];

        try {
            DB::transaction(function () use ($data, $centro, $worker, $uploadedFiles, &$storedPaths): void {
                $lockedWorker = $centro->trabajadores()
                    ->whereKey($worker->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $change = $lockedWorker->cambiosCicloLaboral()->create([
                    'centro_trabajo_id' => $centro->getKey(),
                    'tipo' => $data['tipo'],
                    'fecha_evento' => $data['fecha_evento'],
                    'categoria_relacion_laboral_id' => $data['categoria_relacion_laboral_id'] ?? null,
                    'registrado_por' => auth()->id(),
                ]);

                foreach ($uploadedFiles as $uploadedFile) {
                    $path = $uploadedFile->store(
                        'worker-lifecycle/'.$centro->getKey().'/'.$worker->getKey(),
                        'local',
                    );

                    if ($path === false) {
                        throw new RuntimeException('Unable to store lifecycle evidence.');
                    }

                    $storedPaths[] = $path;

                    $change->evidencias()->create([
                        'trabajador_id' => $worker->getKey(),
                        'centro_trabajo_id' => $centro->getKey(),
                        'ruta' => $path,
                        'nombre_original' => $uploadedFile->getClientOriginalName(),
                        'mime_type' => $uploadedFile->getMimeType() ?? 'application/octet-stream',
                        'tamano_bytes' => $uploadedFile->getSize(),
                        'cargado_por' => auth()->id(),
                    ]);
                }

                $latestChange = $lockedWorker->cambiosCicloLaboral()
                    ->orderByDesc('fecha_evento')
                    ->orderByDesc('id')
                    ->firstOrFail();

                $latestEntry = $lockedWorker->cambiosCicloLaboral()
                    ->whereIn('tipo', ['ingreso', 'reingreso'])
                    ->whereDate('fecha_evento', '<=', $latestChange->fecha_evento->toDateString())
                    ->orderByDesc('fecha_evento')
                    ->orderByDesc('id')
                    ->first();

                $lockedWorker->update([
                    'activo' => $latestChange->tipo !== 'baja',
                    'fecha_ingreso' => $latestEntry?->fecha_evento ?? $lockedWorker->fecha_ingreso,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }

        return response('lifecycle.change.created', 201);
    }

    public function download(int $trabajador, int $evidencia, CentroTrabajoContext $context): SymfonyResponse
    {
        Gate::authorize('personal.modify');

        $centro = $context->activo();
        abort_if($centro === null, 404);

        $worker = $centro->trabajadores()->findOrFail($trabajador);

        $evidence = EvidenciaCicloLaboral::query()
            ->where('trabajador_id', $worker->getKey())
            ->where('centro_trabajo_id', $centro->getKey())
            ->whereHas('cambioCicloLaboral', function (Builder $query) use ($worker, $centro): void {
                $query->where('trabajador_id', $worker->getKey())
                    ->where('centro_trabajo_id', $centro->getKey());
            })
            ->findOrFail($evidencia);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($evidence->ruta), 404);

        return $disk->download($evidence->ruta, $evidence->nombre_original, [
            'Content-Type' => $evidence->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
