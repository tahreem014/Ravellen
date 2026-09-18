<?php

namespace App\Http\Controllers;

use App\Http\Requests\RefundRequest\StoreRefundRequestRequest;
use App\Http\Requests\RefundRequest\UpdateRefundRequestRequest;
use App\Models\OrderItem;
use App\Models\RefundRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function store(StoreRefundRequestRequest $request): JsonResponse
    {
        $orderItem = OrderItem::with(['order', 'refundRequest'])->findOrFail($request->validated('order_item_id'));

        abort_unless($orderItem->order->user_id === $request->user()->id, 403);
        abort_if($orderItem->refundRequest, 422, 'A refund request already exists for this item.');
        abort_unless($orderItem->isRefundEligible(), 422, 'This item is not eligible for a refund.');

        $refund = RefundRequest::create([
            'order_item_id' => $orderItem->id,
            'amount'        => $request->validated('amount'),
            'bank_details'  => $request->validated('bank_details'),
            'reason'        => $request->validated('reason'),
            'status'        => 'pending',
            'requested_at'  => now(),
        ]);

        return response()->json(['success' => true, 'refund' => $refund]);
    }

    public function update(UpdateRefundRequestRequest $request, RefundRequest $refundRequest): JsonResponse
    {
        $refundRequest->load('orderItem.order');

        abort_unless($refundRequest->orderItem->order->user_id === $request->user()->id, 403);
        abort_unless($refundRequest->status === 'pending', 422, 'This refund request can no longer be edited.');

        $refundRequest->update([
            'bank_details' => $request->validated('bank_details', $refundRequest->bank_details),
            'reason'       => $request->validated('reason', $refundRequest->reason),
        ]);

        return response()->json(['success' => true, 'refund' => $refundRequest]);
    }

    public function cancel(Request $request, RefundRequest $refundRequest): JsonResponse
    {
        $refundRequest->load('orderItem.order');

        abort_unless($refundRequest->orderItem->order->user_id === $request->user()->id, 403);
        abort_unless($refundRequest->status === 'pending', 422, 'This refund request can no longer be cancelled.');

        $refundRequest->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'refund' => $refundRequest]);
    }
}
