<?php

namespace App\Providers;

use App\Policies\SensitiveDataPolicy;
use App\Support\CentroTrabajoContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CentroTrabajoContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('personal.register', [SensitiveDataPolicy::class, 'registerPersonal']);
        Gate::define('personal.modify', [SensitiveDataPolicy::class, 'modifyPersonal']);
        Gate::define('personal.delete', [SensitiveDataPolicy::class, 'deletePersonal']);
        Gate::define('personal.approve', [SensitiveDataPolicy::class, 'approvePersonal']);
        Gate::define('nom035.register', [SensitiveDataPolicy::class, 'registerNom035']);
        Gate::define('nom035.modify', [SensitiveDataPolicy::class, 'modifyNom035']);
        Gate::define('audit-inspection.write', [SensitiveDataPolicy::class, 'writeAuditInspection']);
        Gate::define('reports.export', [SensitiveDataPolicy::class, 'exportReports']);
    }
}
