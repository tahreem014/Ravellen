<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function __construct(private readonly LocationService $locations)
    {
    }

    /** GET /locations/{code}/cities — the city list that fills the city field once a country is chosen. */
    public function cities(string $code): JsonResponse
    {
        $country = $this->locations->findCountry($code);

        abort_unless($country, 404);

        return response()
            ->json(['country' => $country['name'], 'cities' => $this->locations->cities($country['code'])])
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
