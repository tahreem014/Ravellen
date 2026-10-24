<?php

namespace App\DataTransferObjects\Order;

use App\Http\Requests\Order\StoreOrderRequest;

final readonly class PlaceOrderData
{
    /**
     * @param int[] $cartItemIds the cart rows the user selected to check out
     */
    public function __construct(
        public string  $shippingName,
        public string  $shippingPhone,
        public string  $shippingAddress,
        public string  $paymentMethod,
        public int     $addressId,
        public array   $cartItemIds,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            shippingName:    $request->validated('shipping_name'),
            shippingPhone:   $request->validated('shipping_phone'),
            shippingAddress: $request->validated('shipping_address'),
            paymentMethod:   $request->validated('payment_method'),
            addressId:       (int) $request->validated('address_id'),
            cartItemIds:     array_map('intval', $request->validated('cart_item_ids')),
        );
    }
}
