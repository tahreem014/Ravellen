<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE       = 'active';
    public const STATUS_OUT_OF_STOCK = 'out_of_stock';
    public const STATUS_PRE_ORDER    = 'pre_order';
    public const STATUS_RESTOCKING   = 'restocking';
    public const STATUS_DISCONTINUED = 'discontinued';

    protected $fillable = [
        'product_id',
        'variant_name',
        'price',
        'min_qty',
        'max_qty',
        'stock',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isPreOrder(): bool
    {
        return $this->status === self::STATUS_PRE_ORDER;
    }

    public function isPurchasable(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PRE_ORDER], true);
    }
}
