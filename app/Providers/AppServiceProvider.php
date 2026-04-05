<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        view()->composer('layouts.admin', function ($view) {
            $pendingCount = \App\Models\Order::where('status', '!=', 'Completed')->where('status', '!=', 'Pending Payment')->count();
            $latest = \App\Models\Order::where('status', '!=', 'Pending Payment')->orderBy('id', 'desc')->first();
            $view->with('pending_orders_count', $pendingCount)
                 ->with('latest_order_id', $latest ? $latest->id : 0);
        });
    }
}
