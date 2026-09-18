<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    public function index(Request $request)
    {
        $refundRequests = RefundRequest::with('orderItem.order.user', 'orderItem.product')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('requested_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.refund-requests.index', [
            'refundRequests' => $refundRequests,
            'statuses' => self::STATUSES,
            'title' => 'Refund Requests',
        ]);
    }

    public function update(Request $request, RefundRequest $refundRequest)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        abort_unless($refundRequest->status === 'pending', 422, 'This refund request has already been processed.');

        $refundRequest->update([
            'status' => $data['status'],
            'processed_at' => now(),
        ]);

        return back()->with('status', 'Refund request ' . $data['status'] . '.');
    }
}
