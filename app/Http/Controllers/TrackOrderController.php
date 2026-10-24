<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Orders are tracked by their tracking number only (the RVL-… reference made
 * at checkout), never by the order id.
 */
class TrackOrderController extends Controller
{
    /**
     * GET /track-order
     * Shows the search box, the user's recent orders, and — if a
     * tracking_number query param matches one of the user's own orders —
     * the tracking result preloaded.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->orderByDesc('order_date')->get();

        $query = trim((string) $request->query('tracking_number', ''));
        $selected = $query !== '' ? $this->findOrder($request->user(), $query) : null;

        return view('pages.track-order', [
            'orders'   => $orders,
            'selected' => $selected ? $this->present($selected) : null,
            'query'    => $query,
        ]);
    }

    /**
     * GET /track-order/lookup?tracking_number=...
     * AJAX lookup used by the search box, scoped to the authenticated
     * user's own orders.
     */
    public function lookup(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('tracking_number', ''));
        $order = $query !== '' ? $this->findOrder($request->user(), $query) : null;

        if (! $order) {
            return response()->json(['message' => 'No order found with that tracking number. Please check it and try again.'], 404);
        }

        return response()->json($this->present($order));
    }

    private function findOrder(User $user, string $trackingNumber): ?Order
    {
        return $user->orders()->where('tracking_number', $trackingNumber)->first();
    }

    private function present(Order $order): array
    {
        return [
            'tracking_number' => $order->tracking_number,
            'order_date'      => $order->order_date?->format('d M Y'),
            'status'          => $order->order_status,
            'status_label'    => $order->status_label,
            'total_amount'    => (float) $order->total_amount,
            'action_url'      => route('orders.show', $order),
            // pre-computed, so the front end only renders it (Order::trackingSteps)
            'timeline'        => $order->trackingSteps(),
        ];
    }
}
