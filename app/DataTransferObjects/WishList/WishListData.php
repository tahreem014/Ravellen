<?php

namespace App\DataTransferObjects\WishList;

use App\Models\WishList;

final readonly class WishListData
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $productName,
        public ?string $productImage,
        public ?float $price,
        public string $createdAt,
    ) {
    }

    public static function fromModel(WishList $wishlist): self
    {
        $product = $wishlist->product;

        return new self(
            id: $wishlist->id,
            productId: $wishlist->product_id,
            productName: $product?->name ?? '',
            productImage: $product?->images->first()?->image,
            price: $product?->cheapestVariant()?->effectivePrice(),
            createdAt: $wishlist->created_at->toDateTimeString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'product_id'    => $this->productId,
            'product_name'  => $this->productName,
            'product_image' => $this->productImage,
            'price'         => $this->price,
            'created_at'    => $this->createdAt,
        ];
    }
}
