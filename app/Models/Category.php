<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Categories the storefront looks up by name (landing pages, "Coming soon" panels, product
    // pages). The admin may still edit them; the Categories page warns that renaming one can
    // empty the shop page that shows it.
    public const STOREFRONT_NAMES = [
        'Gift Boxes',
        'BTS Gift Boxes',
        'Ceramic Items',
        'BTS Merch',
        'Mystery Balls',
        'Mystery Scoops',
    ];

    protected $fillable = [
        'name',
    ];

    protected static function booted()
    {
        // products.category_id cascades, so deleting a category that still has products would
        // delete those products (and with them their variants, order lines, reviews and carts).
        // The admin screen checks first and explains; this stops any other caller from doing it.
        static::deleting(fn (Category $category) => ! $category->hasProducts());
    }

    /** Whether two names are the same as the database (and the storefront's lookups) see them. */
    public static function sameName(?string $a, ?string $b): bool
    {
        return mb_strtolower(trim((string) $a)) === mb_strtolower(trim((string) $b));
    }

    public function isStorefrontLinked(): bool
    {
        return collect(self::STOREFRONT_NAMES)->contains(fn (string $name) => self::sameName($name, $this->name));
    }

    /** Products filed under this category, or under one of its sub-categories. */
    private function productsInTree()
    {
        return Product::where(fn ($query) => $query
            ->where('category_id', $this->id)
            ->orWhereIn('sub_category_id', $this->subCategories()->select('id')));
    }

    public function hasProducts(): bool
    {
        return $this->productsInTree()->exists();
    }

    public function productCount(): int
    {
        return $this->productsInTree()->count();
    }

    /**
     * A category with no products yet: the storefront shows "Coming soon" in place of an
     * empty page. Any product counts, whatever its stock status, so a category whose
     * products are all out of stock lists them as usual instead.
     */
    public function isComingSoon(): bool
    {
        return $this->relationLoaded('products')
            ? $this->products->isEmpty()
            : ! $this->products()->exists();
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
