<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

/**
 * The store-wide figures on the About page, counted from live data (nothing is
 * typed in by hand). The page renders them from summary() and keeps them current
 * by polling GET /live?stats=1 (see layouts/live-updates.blade.php), which answers
 * with the same summary().
 */
class StoreStatsService
{
    /** Category whose units are counted as "Scoops Sold". */
    public const SCOOPS_CATEGORY = 'Mystery Scoops';

    /**
     * Display-ready values keyed by stat name: counts with thousands separators,
     * the rating to one decimal ("—" until there is a first review).
     *
     * @return array{customers: string, scoops: string, categories: string, rating: string}
     */
    public function summary(): array
    {
        $rating = Review::avg('rating');

        return [
            'customers'  => number_format($this->happyCustomers()),
            'scoops'     => number_format($this->scoopsSold()),
            'categories' => number_format(Category::count()),
            'rating'     => $rating === null ? '—' : number_format((float) $rating, 1),
        ];
    }

    /** Customers who have had at least one order delivered. */
    public function happyCustomers(): int
    {
        return Order::where('order_status', Order::STATUS_DELIVERED)->distinct()->count('user_id');
    }

    /**
     * Mystery Scoop units on orders that were actually bought: paid (or, for a
     * pre-order, advance-paid) and not cancelled. Unpaid checkouts don't count.
     */
    public function scoopsSold(): int
    {
        return (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where('categories.name', self::SCOOPS_CATEGORY)
            ->where('orders.order_status', '!=', Order::STATUS_CANCELLED)
            ->whereIn('orders.payment_status', [Order::PAYMENT_PAID, Order::PAYMENT_PARTIALLY_PAID])
            ->sum('order_items.qty');
    }
}
