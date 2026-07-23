<?php

use App\Http\Controllers\OrderMessageController;
use App\Http\Controllers\Provider\DashboardController;
use App\Http\Controllers\Provider\MenuController;
use App\Http\Controllers\Provider\OrderController;
use App\Http\Controllers\Provider\ScheduleController;
use App\Http\Controllers\Provider\StatusController;
use App\Http\Controllers\Provider\StoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:penyedia_jasa'])
    ->prefix('provider')
    ->name('provider.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('/status/toggle', [StatusController::class, 'toggle'])->name('status.toggle');

        Route::get('/schedule', [ScheduleController::class, 'edit'])->name('schedule.edit');
        Route::put('/schedule', [ScheduleController::class, 'update'])->name('schedule.update');

        Route::resource('stores', StoreController::class)->except(['show']);
        Route::resource('menus', MenuController::class)->except(['show']);

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance');
        Route::patch('orders/{order}/report-issue', [OrderController::class, 'reportIssue'])->name('orders.reportIssue');
        Route::post('orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');
    });
