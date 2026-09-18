<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'video',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveMediaUrl($this->image));
    }

    protected function videoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resolveMediaUrl($this->video));
    }

    private function resolveMediaUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://')
            ? $value
            : asset('images/products/' . $value);
    }
}
