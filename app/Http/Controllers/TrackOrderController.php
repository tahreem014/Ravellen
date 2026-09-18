<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    /**
     * GET /track-order
     * Shows the search box, the user's recent orders, and — if an
     * order_id/tracking_number query param is given and matches one of the
     * user's own orders — the tracking result preloaded.
     */
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->orderByDesc('order_date')->get();

        $query = trim((string) $request->query('order_id', $request->query('tracking_number', '')));
        $selected = $query !== '' ? $this->findOrder($request->user(), $query) : null;

        return view('pages.track-order', [
            'orders'   => $orders,
            'selected' => $selected ? $this->present($selected) : null,
            'query'    => $query,
        ]);
    }

    /**
     * GET /track-order/lookup?query=...
     * AJAX lookup used by the search box, scoped to the authenticated
     * user's own orders (matches by order id or tracking number).
     */
    public function lookup(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('query', ''));
        $order = $query !== '' ? $this->findOrder($request->user(), $query) : null;

        if (! $order) {
            return response()->json(['message' => 'Order not found. Please check your order ID or tracking number.'], 404);
        }

        return response()->json($this->present($order));
    }

    private function findOrder(User $user, string $query): ?Order
    {
        return $user->orders()
            ->where(function ($q) use ($query) {
                if (ctype_digit($query)) {
                    $q->orWhere('id', (int) $query);
                }
                $q->orWhere('tracking_number', $query);
            })
            ->first();
    }

    private function present(Order $order): array
    {
        return [
            'id'              => $order->id,
            'tracking_number' => $order->tracking_number,
            'order_date'      => $order->order_date?->format('d M Y'),
            'status'          => $order->order_status,
            'status_label'    => ucwords(str_replace('_', ' ', $order->order_status)),
            'total_amount'    => (float) $order->total_amount,
            'action_url'      => route('orders.show', $order),
            'timeline'        => $this->timeline($order),
        ];
    }

    /**
     * Build an ordered, pre-computed timeline for the order so the front
     * end only has to render it, not decide it. Preorder and normal orders
     * follow different step sequences; cancelled orders short-circuit.
     */
    private function timeline(Order $order): array
    {
        $placed = [
            'label' => 'Order Placed',
            'date'  => $order->order_date?->format('d M Y'),
            'state' => 'done',
        ];

        if ($order->order_status === Order::STATUS_CANCELLED) {
            return [
                $placed,
                [
                    'label' => 'Cancelled',
                    'date'  => $order->updated_at?->format('d M Y'),
                    'state' => 'cancelled',
                ],
            ];
        }

        $steps = $order->isPreOrder()
            ? [
                Order::STATUS_AWAITING_STOCK => 'Awaiting Stock',
                Order::STATUS_PAYMENT_DUE    => 'Payment Due',
                Order::STATUS_PROCESSING     => 'Processing',
                Order::STATUS_SHIPPED        => 'Shipped',
                Order::STATUS_DELIVERED      => 'Delivered',
            ]
            : [
                Order::STATUS_PROCESSING => 'Processing',
                Order::STATUS_SHIPPED    => 'Shipped',
                Order::STATUS_DELIVERED  => 'Delivered',
            ];

        $keys = array_keys($steps);
        $currentIndex = array_search($order->order_status, $keys, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;

        $timeline = [$placed];

        foreach ($keys as $i => $key) {
            $timeline[] = [
                'label' => $steps[$key],
                'date'  => match (true) {
                    $i < $currentIndex  => 'Completed',
                    $i === $currentIndex => 'Current status',
                    default             => 'Pending',
                },
                'state' => match (true) {
                    $i < $currentIndex  => 'done',
                    $i === $currentIndex => 'active',
                    default             => 'pending',
                },
            ];
        }

        return $timeline;
    }
}
