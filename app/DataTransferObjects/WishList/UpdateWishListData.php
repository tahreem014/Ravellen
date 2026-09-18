<?php

namespace App\DataTransferObjects\WishList;

use App\Http\Requests\WishList\UpdateWishListRequest;

final readonly class UpdateWishListData
{
    public function __construct(
        public ?int $productId,
    ) {
    }

    public static function fromRequest(UpdateWishListRequest $request): self
    {
        return new self(
            productId: $request->validated('product_id'),
        );
    }
}
