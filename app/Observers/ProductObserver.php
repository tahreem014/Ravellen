<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\User;
use App\Notifications\NewProductNotification;
use Illuminate\Support\Facades\Notification;

class ProductObserver
{
    /**
     * Notify every user when the admin publishes a new product.
     */
    public function created(Product $product): void
    {
        User::query()->chunkById(200, function ($users) use ($product) {
            Notification::send($users, new NewProductNotification($product));
        });
    }
}
