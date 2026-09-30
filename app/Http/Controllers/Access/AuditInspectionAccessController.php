<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AuditInspectionAccessController extends Controller
{
    public function write(): Response
    {
        Gate::authorize('audit-inspection.write');

        return response('audit-inspection.write.ok', 200);
    }
}
