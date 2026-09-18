<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const TYPE_NORMAL    = 'normal';
    public const TYPE_PRE_ORDER = 'pre_order';

    public const STATUS_PENDING        = 'pending';
    public const STATUS_AWAITING_STOCK = 'awaiting_stock';
    public const STATUS_PAYMENT_DUE    = 'payment_due';
    public const STATUS_PROCESSING     = 'processing';
    public const STATUS_SHIPPED        = 'shipped';
    public const STATUS_DELIVERED      = 'delivered';
    public const STATUS_CANCELLED      = 'cancelled';

    public const STAGE_ADVANCE_PAID = 'advance_paid';
    public const STAGE_COMPLETED    = 'completed';

    protected $fillable = [
        'user_id',
        'order_type',
        'shipping_name',
        'shipping_phone',
        'shipping_fee',
        'shipping_rate_id',
        'shipping_address',
        'total_amount',
        'advance_amount',
        'remaining_amount',
        'order_date',
        'order_status',
        'payment_method',
        'payment_status',
        'payment_stage',
        'tracking_number',
        'delivered_at',
        'remaining_paid_at',
    ];

    protected $casts = [
        'order_date'        => 'datetime',
        'delivered_at'       => 'datetime',
        'remaining_paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingRate()
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function isPreOrder(): bool
    {
        return $this->order_type === self::TYPE_PRE_ORDER;
    }

    /** Orders that have taken the 40% advance and are waiting on restock. */
    public function scopeAwaitingStock($query)
    {
        return $query->where('order_type', self::TYPE_PRE_ORDER)
            ->where('order_status', self::STATUS_AWAITING_STOCK);
    }
}
