<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ReportExportController extends Controller
{
    public function export(): Response
    {
        Gate::authorize('reports.export');

        return response('reports.export.ok', 200);
    }
}
