<?php

use App\Http\Controllers\Access\AnnualReviewController;
use App\Http\Controllers\Access\AuditInspectionAccessController;
use App\Http\Controllers\Access\ReportExportController;
use App\Http\Controllers\Access\SensitiveDataAccessController;
use App\Http\Controllers\CentroTrabajoActivoController;
use App\Http\Controllers\Sst\SstController;
use App\Http\Controllers\Workforce\WorkforceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::livewire('/login', 'pages::auth.login')
    ->middleware('guest')
    ->name('login');

Route::middleware('auth')->group(function (): void {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');

    Route::post('/centro-trabajo-activo', [CentroTrabajoActivoController::class, 'update'])
        ->name('centro-trabajo-activo.update');

    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Route::prefix('acceso')->group(function (): void {
        Route::post('/personal/registrar', [SensitiveDataAccessController::class, 'registerPersonal'])
            ->name('personal.register');
        Route::put('/personal/modificar', [SensitiveDataAccessController::class, 'modifyPersonal'])
            ->name('personal.modify');
        Route::post('/personal/eliminar', [SensitiveDataAccessController::class, 'deletePersonal'])
            ->name('personal.delete');
        Route::post('/personal/aprobar', [SensitiveDataAccessController::class, 'approvePersonal'])
            ->name('personal.approve');

        Route::post('/nom035/registrar', [SensitiveDataAccessController::class, 'registerNom035'])
            ->name('nom035.register');
        Route::put('/nom035/modificar', [SensitiveDataAccessController::class, 'modifyNom035'])
            ->name('nom035.modify');

        Route::post('/auditorias/inspeccion', [AuditInspectionAccessController::class, 'write'])
            ->name('audit-inspection.write');

        Route::post('/reportes/exportar', [ReportExportController::class, 'export'])
            ->name('reports.export');
    });

    Route::post('/admin/annual-review', [AnnualReviewController::class, 'store'])
        ->name('annual-review.store');

    Route::prefix('workforce')->group(function (): void {
        Route::livewire('/workers', 'pages::workforce.workers')
            ->name('workforce.workers');
        Route::livewire('/contracts', 'pages::workforce.contracts')
            ->name('workforce.contracts');

        Route::post('/workers', [WorkforceController::class, 'storeWorker'])
            ->name('workforce.workers.store');
        Route::post('/contracts', [WorkforceController::class, 'storeContract'])
            ->name('workforce.contracts.store');
    });

    Route::prefix('sst')->group(function (): void {
        Route::livewire('/findings', 'pages::sst.findings')
            ->name('sst.findings');
        Route::livewire('/actions', 'pages::sst.actions')
            ->name('sst.actions');
        Route::livewire('/commissions', 'pages::sst.commissions')
            ->name('sst.commissions');
        Route::livewire('/maintenance', 'pages::sst.maintenance')
            ->name('sst.maintenance');

        Route::post('/findings', [SstController::class, 'storeFinding'])
            ->name('sst.findings.store');
        Route::post('/actions', [SstController::class, 'storeAction'])
            ->name('sst.actions.store');
        Route::post('/commissions', [SstController::class, 'storeCommission'])
            ->name('sst.commissions.store');
        Route::post('/maintenance', [SstController::class, 'storeMaintenance'])
            ->name('sst.maintenance.store');
    });
});
