<?php

namespace App\Services;

use App\Models\ShippingRate;

class ShippingRateService
{
    /**
     * Find the active shipping rate for a destination. A city-specific rate
     * takes priority over a country-wide rate (one with a null city).
     */
    public function findActiveRate(?string $country, ?string $city): ?ShippingRate
    {
        if (! $country) {
            return null;
        }

        return ShippingRate::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(country) = ?', [mb_strtolower(trim($country))])
            ->where(function ($query) use ($city) {
                $query->whereNull('city');

                if ($city) {
                    $query->orWhereRaw('LOWER(city) = ?', [mb_strtolower(trim($city))]);
                }
            })
            ->orderByRaw('city IS NULL')
            ->first();
    }
}
