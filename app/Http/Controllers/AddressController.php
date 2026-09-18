<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Address\StoreAddressData;
use App\DataTransferObjects\Address\UpdateAddressData;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $addresses = $this->addressService->listAddresses($request->user())
            ->map(fn ($address) => $address->toArray())
            ->values();

        return response()->json($addresses);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        $data = $this->addressService->getAddress($request->user(), $address);

        return response()->json($data->toArray());
    }

    public function store(StoreAddressRequest $request): RedirectResponse|JsonResponse
    {
        $this->addressService->createAddress($request->user(), StoreAddressData::fromRequest($request));

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Address added successfully.',
                'redirect' => route('profile', ['tab' => 'addresses']),
            ]);
        }

        return redirect()->route('profile', ['tab' => 'addresses'])->with('status', 'Address added successfully.');
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse|JsonResponse
    {
        $this->addressService->updateAddress(
            $request->user(),
            $address,
            UpdateAddressData::fromRequest($request, $address)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Address updated successfully.',
                'redirect' => route('profile', ['tab' => 'addresses']),
            ]);
        }

        return redirect()->route('profile', ['tab' => 'addresses'])->with('status', 'Address updated successfully.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->addressService->deleteAddress($request->user(), $address);

        return redirect()->route('profile', ['tab' => 'addresses'])->with('status', 'Address deleted successfully.');
    }
}
