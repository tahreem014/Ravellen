<?php

namespace App\DataTransferObjects\Order;

final readonly class OrderItemData
{
    public function __construct(
        public int     $productId,
        public ?int    $productVariantId,
        public int     $qty,
        public float   $price,          // unit price already adjusted for preorder
        public ?string $name,
        public ?string $subtitle,
        public ?string $image,
        public bool    $preorder,
        public ?string $comment,
        public ?string $preference,
    ) {}
}
