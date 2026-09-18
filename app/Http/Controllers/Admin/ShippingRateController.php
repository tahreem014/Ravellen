<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;

class ShippingRateController extends Controller
{
    public function index()
    {
        $shippingRates = ShippingRate::query()
            ->orderBy('country')
            ->orderByRaw('city IS NULL')
            ->orderBy('city')
            ->paginate(20);

        return view('admin.shipping-rates.index', compact('shippingRates'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($this->duplicateExists($data)) {
            return back()->withInput()->withErrors(['country' => 'A shipping rate for this country/city already exists.']);
        }

        ShippingRate::create($data);

        return back()->with('status', 'Shipping rate added.');
    }

    public function update(Request $request, ShippingRate $shippingRate)
    {
        $data = $this->validateData($request);

        if ($this->duplicateExists($data, $shippingRate->id)) {
            return back()->withInput()->withErrors(['country' => 'A shipping rate for this country/city already exists.']);
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
            'country'    => ['required', 'string', 'max:255'],
            'city'       => ['nullable', 'string', 'max:255'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'is_active'  => ['sometimes', 'boolean'],
        ]);

        $data['country'] = trim($data['country']);
        $data['city'] = $data['city'] ? trim($data['city']) : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function duplicateExists(array $data, ?int $ignoreId = null): bool
    {
        return ShippingRate::query()
            ->where('country', $data['country'])
            ->where('city', $data['city'])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
