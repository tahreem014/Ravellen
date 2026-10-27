<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ShippingRateService;
use App\Support\CheckoutResume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly ShippingRateService $shippingRateService)
    {
    }

    /**
     * GET /checkout
     * Straight after a guest has logged in or registered from "Place Order", the details they
     * had entered become a saved address (found again, not duplicated, if it already exists),
     * and the page opens on it with the payment step ready to continue.
     */
    public function show(Request $request): View
    {
        $user   = $request->user();
        $resume = $user ? CheckoutResume::pending() : null;
        $resumedAddress = null;

        if ($resume) {
            $resumedAddress = $user->addresses()->firstOrCreate(
                [
                    'name'    => $resume['name'],
                    'phone'   => $resume['phone'],
                    'address' => $resume['address'],
                    'city'    => $resume['city'],
                    'country' => $resume['country'],
                ],
                ['is_default' => ! $user->addresses()->exists()],
            );

            CheckoutResume::forget();
        }

        $addresses      = $user ? $user->addresses()->latest()->get() : collect();
        $defaultAddress = $resumedAddress ?? $addresses->firstWhere('is_default', true) ?? $addresses->first();

        return view('pages.proceed-to-checkout', [
            'addresses'      => $addresses,
            'defaultAddress' => $defaultAddress,
            'resume'         => $resume ? ['payment_method' => $resume['payment_method']] : null,
        ]);
    }

    /**
     * POST /checkout/resume
     * A guest pressed "Place Order": keep what they entered for a while and send them to log in.
     * Whether they log in or register, they come back to checkout (see CheckoutResume).
     */
    public function stash(Request $request): JsonResponse
    {
        $details = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'address'        => 'required|string|max:500',
            'city'           => 'required|string|max:255',
            'country'        => 'required|string|max:255',
            'payment_method' => ['required', 'string', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'phone.regex' => 'Phone number must start with + and country code (e.g. +923001234567).',
        ]);

        CheckoutResume::store($details);

        return response()->json(['redirect' => route('login')]);
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
