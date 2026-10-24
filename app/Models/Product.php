<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    // Mystery Balls pricing: the product sells one "Pack of N" variant per
    // quantity option (5, 10, 15 …). Automatic derives every pack's price from
    // the 5-ball price; custom lets the admin price each pack on its own.
    // A null pricing_mode is a standard product priced per variant.
    public const PRICING_AUTOMATIC = 'automatic';
    public const PRICING_CUSTOM    = 'custom';
    public const PACK_STEP         = 5;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
        'pricing_mode',
    ];

    protected static function booted()
    {
        static::creating(function (Product $product) {
            if ($product->slug) {
                return;
            }

            $base = Str::slug($product->name) ?: 'product';
            $slug = $base;
            $suffix = 2;

            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $product->slug = $slug;
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasManyThrough(Review::class, ProductVariant::class, 'product_id', 'product_variant_id', 'id', 'id');
    }

    public function wishLists()
    {
        return $this->hasMany(WishList::class);
    }

    public function usesPackPricing(): bool
    {
        return in_array($this->pricing_mode, [self::PRICING_AUTOMATIC, self::PRICING_CUSTOM], true);
    }

    public function cheapestVariant(): ?ProductVariant
    {
        return ProductVariant::cheapestOf($this->variants);
    }

    /**
     * Discount badge text for product images: "-15%" when every variant on
     * offer is discounted by the same amount, "Up to -20%" when the discount
     * differs between variants (or some aren't on sale), null with no sale.
     */
    public function discountLabel(): ?string
    {
        $offered  = $this->variants->reject(fn (ProductVariant $variant) => $variant->status === ProductVariant::STATUS_DISCONTINUED);
        $percents = $offered->map(fn (ProductVariant $variant) => $variant->discountPercent());

        if ($percents->filter()->isEmpty()) {
            return null;
        }

        $uniform = $percents->unique()->count() === 1;

        return ($uniform ? '' : 'Up to ') . '-' . $percents->max() . '%';
    }

    /**
     * The pack variants currently offered as quantity options, smallest
     * first. Discontinued packs are quantities the admin has removed.
     *
     * @return \Illuminate\Support\Collection<int, ProductVariant>
     */
    public function packVariants()
    {
        return $this->variants
            ->filter(fn (ProductVariant $variant) => $variant->packSize() !== null
                && $variant->status !== ProductVariant::STATUS_DISCONTINUED)
            ->sortBy(fn (ProductVariant $variant) => $variant->packSize())
            ->values();
    }

    /** Pack options in the shape the product page's quantity stepper reads. */
    public function packOptions(): array
    {
        return $this->packVariants()->map(fn (ProductVariant $variant) => [
            'qty'       => $variant->packSize(),
            'id'        => $variant->id,
            'price'     => $variant->effectivePrice(),
            'original'  => $variant->hasDiscount() ? (float) $variant->price : null,
            'percent'   => $variant->discountPercent(),
            'preorder'  => $variant->isPreOrder(),
            'available' => $variant->isPurchasable(),
        ])->all();
    }
}
