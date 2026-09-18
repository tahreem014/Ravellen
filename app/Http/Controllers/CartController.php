<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Cart\StoreCartItemData;
use App\Http\Requests\AddToCart\StoreAddToCartRequest;
use App\Http\Requests\AddToCart\UpdateAddToCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    /**
     * GET /cart/items
     * Return the authenticated user's cart items as JSON.
     */
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['items' => []]);
        }

        $items = $this->cartService->getItems($request->user());

        return response()->json([
            'items' => $items->map(fn ($item) => $item->toCartShape())->values(),
        ]);
    }

    /**
     * POST /cart/items
     * Add a product (with optional variant) to the cart.
     */
    public function store(StoreAddToCartRequest $request): JsonResponse
    {
        $data = StoreCartItemData::fromRequest($request);

        try {
            $cartItem = $this->cartService->addItem($request->user(), $data);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Item added to cart.',
            'cartItem' => $cartItem->toCartShape(),
        ], 201);
    }

    /**
     * PUT /cart/items/{id}
     * Update quantity (and optionally comment/preference) for a cart item.
     */
    public function update(UpdateAddToCartRequest $request, int $id): JsonResponse
    {
        $cartItem = $this->cartService->updateItem(
            user:       $request->user(),
            cartItemId: $id,
            qty:        (int) $request->validated('qty'),
            comment:    $request->validated('comment'),
            preference: $request->validated('preference'),
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Cart item updated.',
            'cartItem' => $cartItem->toCartShape(),
        ]);
    }

    /**
     * DELETE /cart/items/{id}
     * Remove a single item from the cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
        ]);
    }

    /**
     * DELETE /cart/items
     * Clear the entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared.',
        ]);
    }
}
