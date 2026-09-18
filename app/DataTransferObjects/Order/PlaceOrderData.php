<?php

namespace App\DataTransferObjects\Order;

use App\Http\Requests\Order\StoreOrderRequest;

final readonly class PlaceOrderData
{
    public function __construct(
        public string  $shippingName,
        public string  $shippingPhone,
        public string  $shippingAddress,
        public string  $paymentMethod,
        public int     $addressId,
    ) {}

    public static function fromRequest(StoreOrderRequest $request): self
    {
        return new self(
            shippingName:    $request->validated('shipping_name'),
            shippingPhone:   $request->validated('shipping_phone'),
            shippingAddress: $request->validated('shipping_address'),
            paymentMethod:   $request->validated('payment_method'),
            addressId:       (int) $request->validated('address_id'),
        );
    }
}
