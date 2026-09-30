<?php

use App\Http\Controllers\Access\AnnualReviewController;
use App\Http\Controllers\Access\AuditInspectionAccessController;
use App\Http\Controllers\Access\LegalEvidenceController;
use App\Http\Controllers\Access\ReportExportController;
use App\Http\Controllers\Access\SensitiveDataAccessController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Audit\AuditController;
use App\Http\Controllers\CentroTrabajoActivoController;
use App\Http\Controllers\Sst\SstController;
use App\Http\Controllers\Training\TrainingController;
use App\Http\Controllers\Workforce\WorkforceController;
use App\Http\Controllers\Workforce\WorkforceLifecycleController;
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

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::livewire('/users', 'pages::admin.users')
            ->middleware('role:Administrador')
            ->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])
            ->middleware('role:Administrador')
            ->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])
            ->middleware('role:Administrador')
            ->name('users.update');
        Route::put('/users/{user}/password', [UserManagementController::class, 'updatePassword'])
            ->middleware('role:Administrador')
            ->name('users.password.update');
        Route::livewire('/legal-evidence', 'pages::admin.legal-evidence')
            ->name('legal-evidence.index');
        Route::post('/legal-evidence', [LegalEvidenceController::class, 'store'])
            ->name('legal-evidence.store');
        Route::post('/legal-evidence/{legalEvidence}/revoke', [LegalEvidenceController::class, 'revoke'])
            ->name('legal-evidence.revoke');
    });

    Route::prefix('workforce')->group(function (): void {
        Route::livewire('/workers', 'pages::workforce.workers')
            ->middleware('can:personal.modify')
            ->name('workforce.workers');
        Route::livewire('/contracts', 'pages::workforce.contracts')
            ->middleware('can:personal.modify')
            ->name('workforce.contracts');

        Route::post('/workers', [WorkforceController::class, 'storeWorker'])
            ->name('workforce.workers.store');
        Route::post('/workers/{trabajador}/lifecycle', [WorkforceLifecycleController::class, 'store'])
            ->name('workforce.workers.lifecycle.store');
        Route::get('/workers/{trabajador}/lifecycle-evidence/{evidencia}', [WorkforceLifecycleController::class, 'download'])
            ->name('workforce.workers.lifecycle-evidence.download');
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

    Route::prefix('training')->group(function (): void {
        Route::livewire('/courses', 'pages::training.courses')
            ->name('training.courses');

        Route::post('/courses', [TrainingController::class, 'storeCourse'])
            ->name('training.courses.store');
    });

    Route::prefix('audit')->group(function (): void {
        Route::livewire('/inspections', 'pages::audit.inspections')
            ->name('audit.inspections');
        Route::livewire('/audits', 'pages::audit.audits')
            ->name('audit.audits');
        Route::livewire('/diagnosis', 'pages::audit.diagnosis')
            ->name('audit.diagnosis');

        Route::post('/inspections', [AuditController::class, 'storeInspection'])
            ->name('audit.inspections.store');
        Route::post('/audits', [AuditController::class, 'storeAudit'])
            ->name('audit.audits.store');
        Route::post('/diagnosis', [AuditController::class, 'storeDiagnosis'])
            ->name('audit.diagnosis.store');
    });
});
