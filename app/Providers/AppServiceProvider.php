<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        ProductVariant::observe(ProductVariantObserver::class);
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
    }
}
