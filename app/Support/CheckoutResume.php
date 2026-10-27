<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * A guest who presses "Place Order" is sent to log in or register first. Their shipping
 * details and payment method wait here, in their own session, for a short while; checkout
 * picks them up again once they're signed in. (Their cart stays in their browser.)
 */
class CheckoutResume
{
    private const KEY = 'checkout.resume';

    public const MINUTES = 30;

    public static function store(array $details): void
    {
        Session::put(self::KEY, $details + ['saved_at' => now()->timestamp]);
    }

    /** The saved details, or null when there are none or they have expired. */
    public static function pending(): ?array
    {
        $saved = Session::get(self::KEY);

        if (! is_array($saved) || now()->timestamp - ($saved['saved_at'] ?? 0) > self::MINUTES * 60) {
            Session::forget(self::KEY);

            return null;
        }

        return $saved;
    }

    public static function forget(): void
    {
        Session::forget(self::KEY);
    }
}
