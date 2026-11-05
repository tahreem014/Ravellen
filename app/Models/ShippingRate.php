<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'country',
        'city',
        'amount',
        'is_active',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** The country's default rate, used for every city that has no rate of its own. */
    public function isCountryWide(): bool
    {
        return trim((string) $this->city) === '';
    }

    /** A charge of nothing is how free shipping is recorded: customers see a "Free shipping" badge instead of an amount. */
    public function isFree(): bool
    {
        return (float) $this->amount <= 0;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
