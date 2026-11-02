<?php

namespace App\Services;

use App\Models\ShippingRate;

class ShippingRateService
{
    public function __construct(private readonly LocationService $locations)
    {
    }

    /**
     * Find the active shipping rate for a destination. A city-specific rate takes
     * priority over the country's default rate (the one with no city); null means
     * nothing is set up for that place, i.e. we don't deliver there.
     *
     * Places are compared by meaning, not spelling: "pakistan", "PK" and "Pakistan" are one
     * country, and "Zürich" and "zurich" are one city.
     */
    public function findActiveRate(?string $country, ?string $city): ?ShippingRate
    {
        $countryName = $this->locations->canonicalCountryName($country);

        if ($countryName === '') {
            return null;
        }

        $rates = ShippingRate::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(country) = ?', [mb_strtolower($countryName)])
            ->get();

        $cityKey = LocationService::key($city);

        $cityRate = $cityKey === ''
            ? null
            : $rates->first(fn (ShippingRate $rate) => ! $rate->isCountryWide() && LocationService::key($rate->city) === $cityKey);

        return $cityRate ?? $rates->first(fn (ShippingRate $rate) => $rate->isCountryWide());
    }

    /** What to tell a customer when {@see findActiveRate()} found nothing, naming where they asked for. */
    public function unavailableMessage(?string $country, ?string $city): string
    {
        $place = collect([LocationService::clean($city), $this->locations->canonicalCountryName($country)])
            ->filter()
            ->implode(', ');

        return $place === ''
            ? 'Delivery is not available for this location.'
            : "Delivery is not available to {$place} yet.";
    }
}
