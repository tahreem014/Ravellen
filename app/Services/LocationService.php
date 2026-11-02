<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Countries and cities for the whole world, read from the bundled GeoNames lists in
 * resources/data/locations (refresh them with `php artisan locations:sync`). Nothing here
 * touches the database or an outside service, so address forms and checkout never wait on one.
 */
class LocationService
{
    /** @var array<int, array{code: string, name: string}>|null */
    private ?array $countries = null;

    /** @var array<string, array{code: string, name: string}> */
    private array $lookup = [];

    /** @return array<int, array{code: string, name: string}> every country, A–Z */
    public function countries(): array
    {
        if ($this->countries === null) {
            $this->countries = json_decode(File::get(resource_path('data/locations/countries.json')), true) ?? [];

            foreach ($this->countries as $country) {
                $this->lookup[strtolower($country['code'])] = $country;
                $this->lookup[self::key($country['name'])]  = $country;
            }
        }

        return $this->countries;
    }

    /** A country from its name or two-letter ISO code, in any letter case; null if unknown. */
    public function findCountry(?string $value): ?array
    {
        $this->countries();

        return $this->lookup[self::key($value)] ?? null;
    }

    /** The country's proper name when it is recognised ("pakistan" → "Pakistan"), else the text as typed. */
    public function canonicalCountryName(?string $value): string
    {
        return $this->findCountry($value)['name'] ?? self::clean($value);
    }

    /** @return string[] the country's cities, A–Z; empty if the country is unknown or has none listed */
    public function cities(string $countryCode): array
    {
        $country = $this->findCountry($countryCode);
        $path    = $country ? resource_path('data/locations/cities/' . $country['code'] . '.json') : null;

        return $path && File::exists($path) ? (json_decode(File::get($path), true) ?? []) : [];
    }

    /** The city as the bundled list spells it ("karachi" → "Karachi"); as typed if the list doesn't have it. */
    public function canonicalCityName(?string $country, ?string $city): string
    {
        $city    = self::clean($city);
        $country = $this->findCountry($country);

        if ($city === '' || ! $country) {
            return $city;
        }

        $key = self::key($city);

        foreach ($this->cities($country['code']) as $name) {
            if (self::key($name) === $key) {
                return $name;
            }
        }

        return $city;
    }

    /** Trimmed, with runs of whitespace collapsed. */
    public static function clean(?string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
    }

    /** What two spellings of one place have in common: "  Zürich " and "zurich" share a key. */
    public static function key(?string $value): string
    {
        $lower = mb_strtolower(self::clean($value));
        $ascii = Str::ascii($lower);

        return $ascii !== '' && ! str_contains($ascii, '?') ? $ascii : $lower;
    }
}
