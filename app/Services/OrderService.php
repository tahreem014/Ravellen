<?php

namespace App\Services;

use App\DataTransferObjects\Order\OrderData;
use App\DataTransferObjects\Order\PlaceOrderData;
use App\Models\AddToCart;
use App\Models\Order;
use App\Models\User;
use App\Services\ShippingRateService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    private const ADVANCE_RATE = 0.4;

    public function __construct(private readonly ShippingRateService $shippingRateService)
    {
    }

    /**
     * Place order(s) from the user's DB cart, then clear the cart.
     *
     * A cart is split by variant status: active-variant items become one
     * "normal" order (paid in full), pre_order-variant items become a
     * separate "pre_order" order (40% advance). A cart containing only one
     * kind produces a single order — the split logic has no special case
     * for it, it's just an empty bucket.
     *
     * @return OrderData[]
     * @throws \RuntimeException if the user's cart is empty
     */
    public function placeOrder(User $user, PlaceOrderData $data): array
    {
        return DB::transaction(function () use ($user, $data) {

            $cartItems = $user->cartItems()
                ->with(['product.category', 'productVariant'])
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException('Your cart is empty.');
            }

            $address = $user->addresses()->find($data->addressId);

            if (! $address) {
                throw new \RuntimeException('Selected shipping address was not found.');
            }

            $rate = $this->shippingRateService->findActiveRate($address->country, $address->city);

            if (! $rate) {
                throw new \RuntimeException('Delivery is not available for this location.');
            }

            $shippingFee   = (float) $rate->amount;
            $normalItems   = $cartItems->reject(fn (AddToCart $item) => $item->productVariant?->isPreOrder());
            $preorderItems = $cartItems->filter(fn (AddToCart $item) => $item->productVariant?->isPreOrder());

            $orders = [];

            if ($normalItems->isNotEmpty()) {
                $orders[] = $this->createOrder($user, $data, $normalItems, $shippingFee, $rate->id, isPreorder: false);
            }

            if ($preorderItems->isNotEmpty()) {
                $orders[] = $this->createOrder($user, $data, $preorderItems, $shippingFee, $rate->id, isPreorder: true);
            }

            $user->cartItems()->delete();

            return array_map(fn (Order $order) => OrderData::fromModel($order), $orders);
        });
    }

    /**
     * Collect the remaining 60% on a preorder order that's back in stock.
     *
     * @throws \RuntimeException if the order doesn't belong to the user or
     *         isn't actually waiting on a remaining payment right now
     */
    public function payRemaining(User $user, int $orderId): OrderData
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = $user->orders()->lockForUpdate()->findOrFail($orderId);

            if ($order->order_status !== Order::STATUS_PAYMENT_DUE) {
                throw new \RuntimeException('This order has no remaining payment due right now.');
            }

            $order->update([
                'payment_stage'      => Order::STAGE_COMPLETED,
                'remaining_paid_at'  => now(),
                'order_status'       => Order::STATUS_PROCESSING,
            ]);

            return OrderData::fromModel($order);
        });
    }

    /**
     * @param Collection<int, AddToCart> $items all same order type (normal or pre_order)
     */
    private function createOrder(User $user, PlaceOrderData $data, Collection $items, float $shippingFee, int $shippingRateId, bool $isPreorder): Order
    {
        $subtotal = $items->sum(fn (AddToCart $item) => (float) ($item->productVariant?->price ?? 0) * $item->qty);
        $tracking = 'RVL-' . strtoupper(Str::random(8));

        $order = Order::create([
            'user_id'           => $user->id,
            'order_type'        => $isPreorder ? Order::TYPE_PRE_ORDER : Order::TYPE_NORMAL,
            'shipping_name'     => $data->shippingName,
            'shipping_phone'    => $data->shippingPhone,
            'shipping_fee'      => $shippingFee,
            'shipping_rate_id'  => $shippingRateId,
            'shipping_address'  => $data->shippingAddress,
            'total_amount'      => $subtotal + $shippingFee,
            'advance_amount'    => $isPreorder ? round($subtotal * self::ADVANCE_RATE, 2) : null,
            'remaining_amount'  => $isPreorder ? round($subtotal * (1 - self::ADVANCE_RATE), 2) + $shippingFee : null,
            'order_date'        => now(),
            'order_status'      => $isPreorder ? Order::STATUS_AWAITING_STOCK : Order::STATUS_PROCESSING,
            'payment_method'    => $data->paymentMethod,
            'payment_status'    => 'paid',
            'payment_stage'     => $isPreorder ? Order::STAGE_ADVANCE_PAID : null,
            'tracking_number'   => $tracking,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'qty'                => $item->qty,
                'price'              => (float) ($item->productVariant?->price ?? 0),
                'comment'            => $item->comment,
                'preference'         => $item->preference,
            ]);
        }

        return $order;
    }
}
