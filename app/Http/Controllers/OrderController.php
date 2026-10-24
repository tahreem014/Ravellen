<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Order\OrderData;
use App\DataTransferObjects\Order\PlaceOrderData;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * POST /orders
     * Place order(s) from the current cart contents. A mixed cart produces
     * two orders (normal + pre_order); a single-type cart produces one.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = PlaceOrderData::fromRequest($request);

        try {
            $orders = $this->orderService->placeOrder($request->user(), $data);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully.',
            'orders'  => array_map(fn (OrderData $order) => $order->toArray(), $orders),
        ], 201);
    }

    /**
     * GET /orders/{order}
     * Order detail page — shows status, items, and a "pay remaining
     * balance" action when the order is a preorder waiting on payment.
     */
    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['items.product', 'items.productVariant']);

        return view('pages.order-detail', compact('order'));
    }
}
