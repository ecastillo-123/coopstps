<?php

namespace App\Http\Middleware;

use App\Support\CentroTrabajoContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza que exista un Centro de Trabajo activo antes de resolver la vista.
 */
class ResolveCentroTrabajoActivo
{
    public function __construct(private readonly CentroTrabajoContext $contexto) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null) {
            $this->contexto->activo();
        }

        return $next($request);
    }
}
