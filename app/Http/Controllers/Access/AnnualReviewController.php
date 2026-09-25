<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\AnnualReview;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AnnualReviewController extends Controller
{
    public function store(Request $request): Response
    {
        Gate::authorize('administracion.gestionar');

        $datos = $request->validate([
            'year' => ['required', 'integer', 'min:2000'],
            'outcome' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $existe = AnnualReview::where('year', $datos['year'])->exists();

        AnnualReview::updateOrCreate(
            ['year' => $datos['year']],
            [
                'outcome' => $datos['outcome'],
                'reviewed_by_user_id' => $request->user()->id,
                'notes' => $datos['notes'] ?? null,
                'reviewed_at' => now(),
            ]
        );

        return response('', $existe ? 200 : 201);
    }
}
