<?php

namespace App\Http\Controllers;

use App\Services\LiveUpdateService;
use App\Services\StoreStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function __construct(
        private readonly LiveUpdateService $live,
        private readonly StoreStatsService $stats,
    ) {
    }

    /**
     * GET /live?v=…&products[]=…&categories[]=…&listings[]=…&stats=1
     * Polled by storefront pages every few seconds. Answers with just the
     * version (and the unread notification count) when nothing changed since
     * v, otherwise with fresh data for the products / categories / listings
     * the page shows. A page that shows the About-page figures adds stats=1
     * and gets them with every answer, since orders and reviews don't move
     * the catalog version.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $version = $this->live->version();
        $payload = ['version' => $version, 'changed' => $request->query('v') !== $version];

        if ($request->boolean('stats')) {
            $payload['stats'] = $this->stats->summary();
        }

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
