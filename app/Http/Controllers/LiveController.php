<?php

namespace App\Http\Controllers;

use App\Services\LiveUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function __construct(private readonly LiveUpdateService $live)
    {
    }

    /**
     * GET /live?v=…&products[]=…&categories[]=…&listings[]=…
     * Polled by storefront pages every few seconds. Answers with just the
     * version (and the unread notification count) when nothing changed since
     * v, otherwise with fresh data for the products / categories / listings
     * the page shows.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $version = $this->live->version();
        $payload = ['version' => $version, 'changed' => $request->query('v') !== $version];

        if ($user = $request->user()) {
            $payload['unread'] = $user->unreadNotifications()->count();
            $payload['orders_version'] = $this->live->ordersVersion($user->id);
        }

        if ($payload['changed']) {
            $ids        = $this->ids($request->query('products'), 200);
            $categories = $this->ids($request->query('categories'), 50);
            $listings   = collect((array) $request->query('listings', []))->filter(fn ($l) => is_string($l))->unique()->take(5);

            $payload += [
                'products'   => (object) $this->live->products($ids),
                'categories' => (object) $this->live->categories($categories),
                'listings'   => (object) $listings->mapWithKeys(fn (string $l) => [$l => $this->live->listingSignature($l)])->all(),
            ];
        }

        return response()->json($payload)->header('Cache-Control', 'no-store');
    }

    private function ids(mixed $values, int $limit): array
    {
        return collect((array) $values)->map(fn ($id) => (int) $id)->filter()->unique()->take($limit)->values()->all();
    }
}
