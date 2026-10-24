<?php

namespace App\DataTransferObjects\Cart;

use App\Models\AddToCart;
use App\Models\ProductVariant;

final readonly class CartItemData
{
    public function __construct(
        public int     $id,
        public int     $productId,
        public ?int    $productVariantId,
        public string  $productName,
        public ?string $variantName,
        public string  $productImage,
        public int     $unitPrice,
        public ?int    $originalPrice,   // regular price while the variant is on sale, else null
        public int     $qty,
        public bool    $preorder,
        public ?string $comment,
        public ?string $preference,
    ) {}

    public static function fromModel(AddToCart $cart): self
    {
        $variant = $cart->productVariant;
        $product = $cart->product;

        $image = $product?->images?->first()?->image_url
            ?? 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=600&q=80';

        return new self(
            id:               $cart->id,
            productId:        $cart->product_id,
            productVariantId: $cart->product_variant_id,
            productName:      $product?->name ?? 'Unknown Product',
            variantName:      $variant?->variant_name,
            productImage:     $image,
            unitPrice:        (int) ($variant?->effectivePrice() ?? 0),
            originalPrice:    $variant?->hasDiscount() ? (int) $variant->price : null,
            qty:              $cart->qty,
            preorder:         $variant?->status === ProductVariant::STATUS_PRE_ORDER,
            comment:          $cart->comment,
            preference:       $cart->preference,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'product_id'         => $this->productId,
            'product_variant_id' => $this->productVariantId,
            'product_name'       => $this->productName,
            'variant_name'       => $this->variantName,
            'product_image'      => $this->productImage,
            'unit_price'         => $this->unitPrice,
            'original_price'     => $this->originalPrice,
            'qty'                => $this->qty,
            'preorder'           => $this->preorder,
            'comment'            => $this->comment,
            'preference'         => $this->preference,
        ];
    }

    /** Shape that matches the frontend rv_cart localStorage schema */
    public function toCartShape(): array
    {
        return [
            'id'        => 'db-' . $this->id,
            'productId' => $this->productId,
            'name'      => $this->productName,
            'subtitle'  => $this->variantName ? 'Variant: ' . $this->variantName : null,
            'image'     => $this->productImage,
            'unitPrice' => $this->unitPrice,
            'originalPrice' => $this->originalPrice,
            'qty'       => $this->qty,
            'preorder'  => $this->preorder,
            'comment'   => $this->comment,
        ];
    }
}
