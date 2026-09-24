<?php

use App\Http\Controllers\CentroTrabajoActivoController;
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
});
