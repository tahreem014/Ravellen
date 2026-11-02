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

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
