<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private const STATUSES = [
        Order::STATUS_PENDING,
        Order::STATUS_AWAITING_STOCK,
        Order::STATUS_PAYMENT_DUE,
        Order::STATUS_CONFIRMED,
        Order::STATUS_PROCESSING,
        Order::STATUS_SHIPPED,
        Order::STATUS_OUT_FOR_DELIVERY,
        Order::STATUS_DELIVERED,
        Order::STATUS_CANCELLED,
    ];

    public function index(Request $request)
    {
        $orders = Order::with('user')
            ->where('order_type', Order::TYPE_NORMAL)
            ->when($request->filled('status'), fn ($q) => $q->where('order_status', $request->status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'title' => 'Orders',
        ]);
    }

    public function preOrders(Request $request)
    {
        $orders = Order::with('user')
            ->where('order_type', Order::TYPE_PRE_ORDER)
            ->when($request->filled('status'), fn ($q) => $q->where('order_status', $request->status))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.pre-orders', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'title' => 'Pre-Orders',
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.productVariant']);

        return view('admin.orders.show', compact('order'), ['statuses' => self::STATUSES]);
    }

    /**
     * Delivery is handled by hand, so the admin just moves the order to its
     * next status; the customer sees it in their account (no emails). The
     * order's tracking number is Ravellen's own reference from checkout, so
     * it isn't edited here.
     */
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'order_status' => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);

        // the delivered date follows the status, also when a mistake is corrected
        if ($data['order_status'] !== $order->order_status) {
            $data['delivered_at'] = $data['order_status'] === Order::STATUS_DELIVERED ? now() : null;
        }

        $order->update($data);

        return back()->with('status', 'Order updated.');
    }

    public function collectRemaining(Order $order)
    {
        if ($order->order_status !== Order::STATUS_PAYMENT_DUE) {
            return back()->withErrors(['order' => 'This order has no remaining payment due right now.']);
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_stage' => Order::STAGE_COMPLETED,
                'remaining_paid_at' => now(),
                'order_status' => Order::STATUS_PROCESSING,
            ]);
        });

        return back()->with('status', 'Remaining payment collected — order moved to processing.');
    }
}
