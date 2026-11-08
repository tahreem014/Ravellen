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

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_OUT_OF_STOCK,
        self::STATUS_PRE_ORDER,
        self::STATUS_RESTOCKING,
        self::STATUS_DISCONTINUED,
    ];

    /** Shoppers are told how many are left once a variant is down to this many (or fewer). */
    public const LOW_STOCK_THRESHOLD = 15;

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

    protected static function booted(): void
    {
        static::saving(fn (self $variant) => $variant->syncStatusWithStock());
    }

    /**
     * A variant that runs out of stock goes out of stock: whenever a save sets the stock to 0 (the
     * last units sold, or the admin entering 0) an active variant becomes Out of stock. Getting it
     * back on sale is a decision — the admin sets it Active again (the form does that as soon as
     * they enter new stock), or a cancelled order's units coming back do (StockService::release).
     *
     * Only an active variant is moved: pre-order, restocking and discontinued are choices the admin
     * made. A save that leaves the stock alone never touches the status.
     */
    public function syncStatusWithStock(): void
    {
        if ($this->isDirty('stock') && (int) $this->stock <= 0 && $this->status === self::STATUS_ACTIVE) {
            $this->status = self::STATUS_OUT_OF_STOCK;
        }
    }

    /**
     * Units a shopper can still order, or null when stock is not what limits this variant
     * (a pre-order is taken without stock, the other statuses cannot be ordered at all).
     */
    public function remainingStock(): ?int
    {
        return $this->status === self::STATUS_ACTIVE ? max(0, (int) $this->stock) : null;
    }

    /** "Only 12 left" while an orderable variant is down to its last few units, else null. */
    public function lowStockLabel(): ?string
    {
        $left = $this->remainingStock();

        return $left !== null && $left > 0 && $left <= self::LOW_STOCK_THRESHOLD ? "Only {$left} left" : null;
    }

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

    /** The fewest a shopper may order of this variant (the admin's "Min"; never below 1). */
    public function orderMin(): int
    {
        return max(1, (int) $this->min_qty);
    }

    /** The most a shopper may order of this variant (the admin's "Max"; never below its minimum). */
    public function orderMax(): int
    {
        return max($this->orderMin(), (int) $this->max_qty);
    }

    public function isPurchasable(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PRE_ORDER], true);
    }

    /** Why shoppers can't buy this variant right now ("Out of stock", "Restocking", …), null while they can. */
    public function unavailableLabel(): ?string
    {
        if ($this->isPurchasable()) {
            return null;
        }

        return match ($this->status) {
            self::STATUS_OUT_OF_STOCK => 'Out of stock',
            self::STATUS_RESTOCKING   => 'Restocking',
            default                   => 'Unavailable',
        };
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
