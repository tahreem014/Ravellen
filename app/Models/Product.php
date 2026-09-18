<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'name',
        'slug',
        'description',
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
}
