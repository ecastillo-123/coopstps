<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class SensitiveDataAccessController extends Controller
{
    public function registerPersonal(): Response
    {
        Gate::authorize('personal.register');

        return response('personal.register.ok', 200);
    }

    public function modifyPersonal(): Response
    {
        Gate::authorize('personal.modify');

        return response('personal.modify.ok', 200);
    }

    public function deletePersonal(): Response
    {
        Gate::authorize('personal.delete');

        return response('personal.delete.ok', 200);
    }

    public function approvePersonal(): Response
    {
        Gate::authorize('personal.approve');

        return response('personal.approve.ok', 200);
    }

    public function registerNom035(): Response
    {
        Gate::authorize('nom035.register');

        return response('nom035.register.ok', 200);
    }

    public function modifyNom035(): Response
    {
        Gate::authorize('nom035.modify');

        return response('nom035.modify.ok', 200);
    }
}
