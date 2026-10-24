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
     * Place order(s) from the cart items the user selected, then remove only
     * those items from the cart — anything left unselected stays put.
     *
     * The selection is split by variant status: active-variant items become
     * one "normal" order (paid in full), pre_order-variant items become a
     * separate "pre_order" order (40% advance). A selection containing only
     * one kind produces a single order — the split logic has no special case
     * for it, it's just an empty bucket.
     *
     * Ids are looked up through the user's own cart relation, so an id that
     * belongs to someone else (or no longer exists) is simply not found.
     *
     * @return OrderData[]
     * @throws \RuntimeException if none of the selected items are in the user's cart
     */
    public function placeOrder(User $user, PlaceOrderData $data): array
    {
        return DB::transaction(function () use ($user, $data) {

            $cartItems = $user->cartItems()
                ->whereIn('id', $data->cartItemIds)
                ->with(['product.category', 'productVariant'])
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException('The selected items are no longer in your cart.');
            }

            // Prices come from the variant, so a row whose variant was removed
            // or taken off sale can't be priced — never let it through at PKR 0.
            if ($cartItems->contains(fn (AddToCart $item) => ! $item->productVariant?->isPurchasable())) {
                throw new \RuntimeException('Some selected items are no longer available. Please remove them from your cart and try again.');
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

            $user->cartItems()->whereIn('id', $cartItems->modelKeys())->delete();

            return array_map(fn (Order $order) => OrderData::fromModel($order), $orders);
        });
    }

    /**
     * @param Collection<int, AddToCart> $items all same order type (normal or pre_order)
     */
    private function createOrder(User $user, PlaceOrderData $data, Collection $items, float $shippingFee, int $shippingRateId, bool $isPreorder): Order
    {
        // effectivePrice() is the sale price while one is set — always read
        // from the DB here, never from what the browser displayed.
        $subtotal = $items->sum(fn (AddToCart $item) => $item->productVariant->effectivePrice() * $item->qty);
        $tracking = 'RVL-' . strtoupper(Str::random(8));

        $order = Order::create([
            'user_id'           => $user->id,
            'order_type'        => $isPreorder ? Order::TYPE_PRE_ORDER : Order::TYPE_NORMAL,
            'shipping_name'     => $data->shippingName,
            'shipping_phone'    => $data->shippingPhone,
            'shipping_fee'      => number_format($shippingFee, 2, '.', ''),
            'shipping_rate_id'  => $shippingRateId,
            'shipping_address'  => $data->shippingAddress,
            'total_amount'      => number_format($subtotal + $shippingFee, 2, '.', ''),
            'advance_amount'    => $isPreorder ? number_format($subtotal * self::ADVANCE_RATE, 2, '.', '') : null,
            'remaining_amount'  => $isPreorder ? number_format($subtotal * (1 - self::ADVANCE_RATE) + $shippingFee, 2, '.', '') : null,
            'order_date'        => now(),
            // Payment hasn't happened yet — Safepay checkout is initiated
            // as a separate step right after the order is created, and
            // these move to their paid/awaiting-stock equivalents only
            // once that payment is verified.
            'order_status'      => Order::STATUS_PENDING,
            'payment_method'    => $data->paymentMethod,
            'payment_status'    => Order::PAYMENT_PENDING,
            'payment_stage'     => null,
            'tracking_number'   => $tracking,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id'         => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'qty'                => $item->qty,
                'price'              => $item->productVariant->effectivePrice(),
                'comment'            => $item->comment,
                'preference'         => $item->preference,
            ]);
        }

        return $order;
    }
}
