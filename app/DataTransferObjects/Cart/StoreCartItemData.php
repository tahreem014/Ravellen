<?php

namespace App\DataTransferObjects\Cart;

use App\Http\Requests\AddToCart\StoreAddToCartRequest;

final readonly class StoreCartItemData
{
    public function __construct(
        public int     $productId,
        public ?int    $productVariantId,
        public int     $qty,
        public ?string $comment,
        public ?string $preference,
    ) {}

    public static function fromRequest(StoreAddToCartRequest $request): self
    {
        return new self(
            productId:        (int) $request->validated('product_id'),
            productVariantId: $request->validated('product_variant_id')
                ? (int) $request->validated('product_variant_id')
                : null,
            qty:              (int) $request->validated('qty'),
            comment:          $request->validated('comment'),
            preference:       $request->validated('preference'),
        );
    }
}
