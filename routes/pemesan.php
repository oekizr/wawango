<?php

use App\Http\Controllers\OrderMessageController;
use App\Http\Controllers\Pemesan\DashboardController;
use App\Http\Controllers\Pemesan\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:pemesan'])
    ->prefix('pemesan')
    ->name('pemesan.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');
    });
