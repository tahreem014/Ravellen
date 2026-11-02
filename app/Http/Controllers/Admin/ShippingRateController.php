<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Rules\KnownCountry;
use App\Services\LocationService;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function __construct(private readonly LocationService $locations)
    {
    }

    public function index(Request $request)
    {
        $search = LocationService::clean($request->query('q'));

        $shippingRates = ShippingRate::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('country', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%")
            ))
            ->orderBy('country')
            ->orderByRaw('city IS NOT NULL')   // each country's default rate first, then its cities
            ->orderBy('city')
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipping-rates.index', compact('shippingRates', 'search'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($this->duplicateExists($data)) {
            return back()->withInput()->withErrors(['country' => $this->duplicateMessage($data)]);
        }

        ShippingRate::create($data);

        return back()->with('status', 'Shipping rate added.');
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $data = $this->validateData($request);

        if ($this->duplicateExists($data, $shippingRate->id)) {
            return back()->withInput()->withErrors(['country' => $this->duplicateMessage($data)]);
        }

        $shippingRate->update($data);

        return back()->with('status', 'Shipping rate updated.');
    }

    public function toggleActive(ShippingRate $shippingRate)
    {
        $shippingRate->update(['is_active' => ! $shippingRate->is_active]);

        return back()->with('status', $shippingRate->is_active ? 'Shipping rate activated.' : 'Shipping rate deactivated.');
    }

    public function destroy(ShippingRate $shippingRate)
    {
        $shippingRate->delete();

        return back()->with('status', 'Shipping rate deleted.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'country'    => ['required', 'string', 'max:255', new KnownCountry],
            'city'       => ['nullable', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        // Stored the way the location list spells them, so the admin page and the customer's
        // address form always agree. A city the list doesn't know is kept as typed.
        $country = $this->locations->canonicalCountryName($data['country']);
        $city    = $this->locations->canonicalCityName($country, $data['city'] ?? null);

        return [
            'country'   => $country,
            'city'      => $city === '' ? null : $city,
            'amount'    => $data['amount'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /** One rate per country (city left empty) and one per city, however the name is cased or accented. */
    private function duplicateExists(array $data, ?int $ignoreId = null): bool
    {
        return ShippingRate::query()
            ->whereRaw('LOWER(country) = ?', [mb_strtolower($data['country'])])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->get()
            ->contains(fn (ShippingRate $rate) => LocationService::key($rate->city) === LocationService::key($data['city']));
    }

    private function duplicateMessage(array $data): string
    {
        return $data['city']
            ? "There is already a shipping rate for {$data['city']}, {$data['country']}. Edit it instead."
            : "{$data['country']} already has a default rate. Edit it instead.";
    }
}
