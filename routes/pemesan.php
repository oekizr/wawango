<?php

use App\Http\Controllers\Pemesan\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:pemesan'])
    ->prefix('pemesan')
    ->name('pemesan.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
