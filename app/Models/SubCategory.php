<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    // Sub-categories the storefront relies on by name (its filter tabs, and the BTS Merch bias
    // pages), per category. The admin may still edit them; the Categories page warns about it.
    // Keep the BTS Merch list in step with HomeController::BTS_BIAS_SUB_CATEGORIES.
    public const STOREFRONT_NAMES = [
        'Gift Boxes'     => ['Kuromi', 'Hello Kitty', 'Cinnamoroll', 'My Melody', 'Tom & Jerry', 'Ben 10', 'Pokémon', 'Harry Potter'],
        'BTS Gift Boxes' => ['Era Boxes', 'BT21 World', 'OT7 Special'],
        'Ceramic Items'  => ['Mugs', 'Bowls', 'Spoons'],
        'BTS Merch'      => ['Jungkook', 'V (Taehyung)', 'Jimin', 'RM (Namjoon)', 'Jin', 'J-Hope', 'SUGA', 'OT7', 'BT21'],
    ];

    protected $fillable = [
        'category_id',
        'name',
    ];

    public function isStorefrontLinked(): bool
    {
        foreach (self::STOREFRONT_NAMES as $categoryName => $names) {
            if (Category::sameName($categoryName, $this->category?->name)
                && collect($names)->contains(fn (string $name) => Category::sameName($name, $this->name))) {
                return true;
            }
        }

        return false;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
