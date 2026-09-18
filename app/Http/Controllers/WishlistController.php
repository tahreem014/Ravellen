<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\WishList\UpdateWishListData;
use App\DataTransferObjects\WishList\WishListData;
use App\Http\Requests\WishList\UpdateWishListRequest;
use App\Models\WishList;
use App\Services\WishListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private readonly WishListService $wishListService)
    {
    }

    public function index(Request $request)
    {
        $wishlist = $request->user()
            ? $this->wishListService->listForDisplay($request->user())
            : session('wishlist', []);

        return view('pages.wish-list', compact('wishlist'));
    }

    /**
     * GET /wishlist
     * List the authenticated user's wish_lists table rows (DB-backed).
     */
    public function list(Request $request): JsonResponse
    {
        $wishlist = $this->wishListService->listWishlist($request->user())
            ->map(fn (WishListData $item) => $item->toArray())
            ->values();

        return response()->json($wishlist);
    }

    /**
     * PUT /wishlist/{wishlist}
     * Update which product a wish_lists row points to.
     */
    public function update(UpdateWishListRequest $request, WishList $wishlist): JsonResponse
    {
        $data = $this->wishListService->updateWishlist(
            $request->user(),
            $wishlist,
            UpdateWishListData::fromRequest($request)
        );

        return response()->json([
            'message'  => 'Wishlist item updated successfully.',
            'wishlist' => $data->toArray(),
        ]);
    }

    /**
     * DELETE /wishlist/{wishlist}
     * Remove a wish_lists row belonging to the authenticated user.
     */
    public function destroy(Request $request, WishList $wishlist): JsonResponse
    {
        $this->wishListService->deleteWishlist($request->user(), $wishlist);

        return response()->json([
            'message' => 'Wishlist item deleted successfully.',
        ]);
    }

    public function toggle(Request $request)
    {
        $item = $request->validate([
            'id'    => 'required|string',
            'name'  => 'required|string',
            'price' => 'required|string',
            'image' => 'required|string',
        ]);

        $productId = $this->extractProductId($item['id']);

        if ($request->user() && $productId !== null) {
            $inWishlist = $this->wishListService->toggleProduct($request->user(), $productId);

            return response()->json(['inWishlist' => $inWishlist, 'count' => $request->user()->wishLists()->count()]);
        }

        $wishlist = session('wishlist', []);

        if (isset($wishlist[$item['id']])) {
            unset($wishlist[$item['id']]);
            $inWishlist = false;
        } else {
            $wishlist[$item['id']] = $item;
            $inWishlist = true;
        }

        session(['wishlist' => $wishlist]);

        return response()->json(['inWishlist' => $inWishlist, 'count' => count($wishlist)]);
    }

    public function remove(Request $request)
    {
        $id = $request->input('id');
        $productId = $this->extractProductId((string) $id);

        if ($request->user() && $productId !== null) {
            $this->wishListService->removeProduct($request->user(), $productId);

            return response()->json(['success' => true, 'count' => $request->user()->wishLists()->count()]);
        }

        $wishlist = session('wishlist', []);
        unset($wishlist[$id]);
        session(['wishlist' => $wishlist]);

        return response()->json(['success' => true, 'count' => count($wishlist)]);
    }

    /**
     * Heart buttons key wishlist items as "p-{product_id}". Returns the
     * numeric product id, or null if the id isn't a real product (e.g. a
     * guest-only/legacy entry that has no row in the wish_lists table).
     */
    private function extractProductId(string $id): ?int
    {
        return preg_match('/^p-(\d+)$/', $id, $m) ? (int) $m[1] : null;
    }
}
