<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    public const TYPE_NORMAL    = 'normal';
    public const TYPE_PRE_ORDER = 'pre_order';

    public const STATUS_PENDING        = 'pending';
    public const STATUS_AWAITING_STOCK = 'awaiting_stock';
    public const STATUS_PAYMENT_DUE    = 'payment_due';
    public const STATUS_CONFIRMED      = 'confirmed';
    public const STATUS_PROCESSING     = 'processing';
    public const STATUS_SHIPPED        = 'shipped';
    public const STATUS_DELIVERED      = 'delivered';
    public const STATUS_CANCELLED      = 'cancelled';

    public const STAGE_ADVANCE_PAID = 'advance_paid';
    public const STAGE_COMPLETED    = 'completed';

    public const PAYMENT_PENDING        = 'pending';
    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_PAID           = 'paid';
    public const PAYMENT_FAILED         = 'failed';
    public const PAYMENT_CANCELLED      = 'cancelled';

    /** Checkout payment options: stored key => customer-facing label. */
    public const PAYMENT_METHODS = [
        'credit_card' => 'Credit / Debit Card',
        'easypaisa'   => 'Easypaisa',
    ];

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
        'payment_reference',
        'remaining_payment_reference',
        'tracking_number',
        'delivered_at',
        'paid_at',
        'remaining_paid_at',
    ];

    protected $casts = [
        'order_date'        => 'datetime',
        'delivered_at'      => 'datetime',
        'paid_at'           => 'datetime',
        'remaining_paid_at' => 'datetime',
        'shipping_fee'      => 'decimal:2',
        'total_amount'      => 'decimal:2',
        'advance_amount'    => 'decimal:2',
        'remaining_amount'  => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->uuid ??= (string) Str::uuid();
        });
    }

    /**
     * Customer-facing routes bind by the random uuid, never the sequential
     * id. Admin routes opt back into the id explicitly ({order:id}).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

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

    /** Falls back to a headline for older orders placed with a since-retired method (visa, nayapay, ...). */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? Str::headline((string) $this->payment_method);
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

    /** The amount that must be charged the first time this order is paid for. */
    public function initialPaymentAmount(): float
    {
        return $this->isPreOrder() ? (float) $this->advance_amount : (float) $this->total_amount;
    }

    /**
     * True while this order's first charge (full payment, or preorder
     * advance) has not yet succeeded — i.e. it's still safe to (re)initiate
     * a Safepay payment for it.
     */
    public function isAwaitingInitialPayment(): bool
    {
        return $this->payment_stage === null
            && in_array($this->payment_status, [self::PAYMENT_PENDING, self::PAYMENT_FAILED, self::PAYMENT_CANCELLED], true);
    }

    /** True while a preorder has been restocked and the 60% balance is due. */
    public function isAwaitingRemainingPayment(): bool
    {
        return $this->isPreOrder()
            && $this->order_status === self::STATUS_PAYMENT_DUE
            && $this->payment_stage !== self::STAGE_COMPLETED;
    }
}
