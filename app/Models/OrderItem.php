<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'qty',
        'price',
        'comment',
        'preference',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function refundRequest()
    {
        return $this->hasOne(RefundRequest::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function canBeReviewed(): bool
    {
        return $this->order?->order_status === Order::STATUS_DELIVERED && ! $this->review;
    }

    public function isRefundEligible(): bool
    {
        $status = $this->order?->order_status;

        if (in_array($status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING], true)) {
            return true;
        }

        if ($status === Order::STATUS_AWAITING_STOCK) {
            return $this->order->order_date && $this->order->order_date->addDays(5)->isAfter(now());
        }

        return false;
    }
}
