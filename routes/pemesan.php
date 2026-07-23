<?php

use App\Http\Controllers\OrderMessageController;
use App\Http\Controllers\Pemesan\DashboardController;
use App\Http\Controllers\Pemesan\OrderController;
use App\Http\Controllers\Pemesan\PaymentController;
use App\Http\Controllers\Pemesan\ProviderController;
use App\Http\Controllers\Pemesan\StoreController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:pemesan'])
    ->prefix('pemesan')
    ->name('pemesan.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');
        Route::get('providers/{provider}', [ProviderController::class, 'show'])->name('providers.show');

        Route::get('stores/{store}', [StoreController::class, 'show'])->name('stores.show');

        Route::get('checkout', [OrderController::class, 'checkout'])->name('checkout.show');
        Route::post('checkout', [OrderController::class, 'store'])->name('checkout.store');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/messages', [OrderMessageController::class, 'store'])->name('orders.messages.store');
        Route::post('orders/{order}/payment-proof', [PaymentController::class, 'store'])->name('orders.paymentProof.store');
    });
