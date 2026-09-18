<?php

namespace App\Http\Controllers;

use App\Services\ShippingRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly ShippingRateService $shippingRateService)
    {
    }

    public function shippingFee(Request $request): JsonResponse
    {
        $address = $request->user()->addresses()->find($request->integer('address_id'));

        if (! $address) {
            return response()->json(['available' => false, 'message' => 'Address not found.'], 404);
        }

        $rate = $this->shippingRateService->findActiveRate($address->country, $address->city);

        if (! $rate) {
            return response()->json([
                'available' => false,
                'message'   => 'Delivery is not available for this location.',
            ], 422);
        }

        return response()->json([
            'available'    => true,
            'shipping_fee' => (float) $rate->amount,
        ]);
    }
}
