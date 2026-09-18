<?php

namespace App\Http\Controllers;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $orderItem = OrderItem::with('order')->findOrFail($request->validated('order_item_id'));

        abort_unless($orderItem->order->user_id === $request->user()->id, 403);
        abort_unless($orderItem->canBeReviewed(), 422, 'This item is not eligible for a review.');

        $review = Review::create([
            'user_id'            => $request->user()->id,
            'product_variant_id' => $orderItem->product_variant_id,
            'order_item_id'      => $orderItem->id,
            'rating'             => $request->validated('rating'),
            'comments'           => $request->validated('comments'),
        ]);

        foreach ($request->file('images', []) as $image) {
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/reviews'), $filename);

            $review->images()->create(['image_url' => $filename]);
        }

        return response()->json(['success' => true, 'review' => $review->load('images')]);
    }
}
