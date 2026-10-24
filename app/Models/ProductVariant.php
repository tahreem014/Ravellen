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
        'sale_price',
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

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    /** Whole-number percentage off the regular price while on sale (1500 → 1200 = 20), else null. */
    public function discountPercent(): ?int
    {
        if (! $this->hasDiscount() || (float) $this->price <= 0) {
            return null;
        }

        $percent = (int) round((1 - (float) $this->sale_price / (float) $this->price) * 100);

        return $percent > 0 ? $percent : null;
    }

    /** What the shopper actually pays per unit — the sale price while one is set. */
    public function effectivePrice(): float
    {
        return (float) ($this->hasDiscount() ? $this->sale_price : $this->price);
    }

    /** Ball count of a Mystery Balls pack variant ("Pack of 10" → 10), null for any other variant. */
    public function packSize(): ?int
    {
        return preg_match('/^Pack of (\d+)$/i', trim($this->variant_name), $m) ? (int) $m[1] : null;
    }

    public static function packName(int $qty): string
    {
        return "Pack of {$qty}";
    }

    /** The variant with the lowest effective price — what "From PKR …" labels show. */
    public static function cheapestOf(iterable $variants): ?self
    {
        return collect($variants)->sortBy(fn (self $variant) => $variant->effectivePrice())->first();
    }
}
