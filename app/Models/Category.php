<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Categories with nothing on sale yet: the storefront shows "Coming soon" in place of
    // their products (category name => route of its landing page). The products stay in the
    // database, so take a category out of this list to put it back on sale.
    public const COMING_SOON = [
        'Ceramic Items' => 'ceramic-items',
    ];

    protected $fillable = [
        'name',
    ];

    public static function nameIsComingSoon(?string $name): bool
    {
        return $name !== null && array_key_exists($name, self::COMING_SOON);
    }

    public function isComingSoon(): bool
    {
        return self::nameIsComingSoon($this->name);
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
