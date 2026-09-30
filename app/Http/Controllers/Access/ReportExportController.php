<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use App\Models\Capacitacion;
use App\Models\DiagnosticoIntegral;
use App\Models\Inspeccion;
use App\Support\CentroTrabajoContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function export(Request $request, CentroTrabajoContext $context): StreamedResponse
    {
        Gate::authorize('reports.export');

        $centro = $context->activo();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="report.csv"',
        ];

        $callback = function () use ($centro): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['modulo', 'titulo', 'fecha', 'centro']);

            if ($centro !== null) {
                foreach (Capacitacion::query()->where('centro_trabajo_id', $centro->getKey())->cursor() as $registro) {
                    fputcsv($handle, ['capacitacion', $registro->nombre, $registro->fecha_inicio?->toDateString(), $centro->nombre]);
                }

                foreach (Inspeccion::query()->where('centro_trabajo_id', $centro->getKey())->cursor() as $registro) {
                    fputcsv($handle, ['inspeccion', $registro->titulo, $registro->fecha?->toDateString(), $centro->nombre]);
                }

                foreach (Auditoria::query()->where('centro_trabajo_id', $centro->getKey())->cursor() as $registro) {
                    fputcsv($handle, ['auditoria', $registro->titulo, $registro->fecha?->toDateString(), $centro->nombre]);
                }

                foreach (DiagnosticoIntegral::query()->where('centro_trabajo_id', $centro->getKey())->cursor() as $registro) {
                    fputcsv($handle, ['diagnostico_integral', $registro->titulo, $registro->fecha?->toDateString(), $centro->nombre]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
