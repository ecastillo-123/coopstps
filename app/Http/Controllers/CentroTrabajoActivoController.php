<?php

namespace App\Http\Controllers;

use App\Support\CentroTrabajoContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CentroTrabajoActivoController extends Controller
{
    public function update(Request $request, CentroTrabajoContext $contexto): RedirectResponse
    {
        $datos = $request->validate([
            'centro_trabajo_id' => ['required', 'integer'],
        ]);

        $centro = $request->user()
            ->centrosTrabajo()
            ->whereKey($datos['centro_trabajo_id'])
            ->firstOrFail();

        $contexto->set($centro);

        return back();
    }
}
