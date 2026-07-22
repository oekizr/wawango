<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Provider;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Observers\ProviderObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        User::observe(UserObserver::class);
        Provider::observe(ProviderObserver::class);
        Order::observe(OrderObserver::class);
    }
}
