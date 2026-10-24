<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\RefundRequest;
use App\Models\SubCategory;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductVariantObserver;
use App\Services\LiveUpdateService;
use App\Services\Safepay\SafepayService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SafepayService::class, function () {
            return new SafepayService(config('services.safepay'));
        });
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

        // Any catalog change lets open storefront pages know to update themselves.
        foreach ([Product::class, ProductVariant::class, ProductImage::class, Category::class, SubCategory::class] as $model) {
            $model::saved(fn () => app(LiveUpdateService::class)->bump());
            $model::deleted(fn () => app(LiveUpdateService::class)->bump());
        }

        // A changed order or refund lets that customer's open order pages update themselves.
        $bumpOrders = fn (?int $userId) => app(LiveUpdateService::class)->bumpOrders($userId);
        Order::saved(fn (Order $order) => $bumpOrders($order->user_id));
        Order::deleted(fn (Order $order) => $bumpOrders($order->user_id));
        RefundRequest::saved(fn (RefundRequest $refund) => $bumpOrders($refund->orderItem?->order?->user_id));
        RefundRequest::deleted(fn (RefundRequest $refund) => $bumpOrders($refund->orderItem?->order?->user_id));
    }
}
