<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Payment\RemainingPaymentDTO;
use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Http\Requests\Payment\RemainingPaymentRequest;
use App\Models\Order;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * POST /orders/{order}/pay
     * Opens (or resumes) the Safepay checkout session for this order's
     * first charge — full payment for a normal order, advance for a
     * preorder. Ownership and payability are re-checked server-side.
     */
    public function initiate(InitiatePaymentRequest $request, Order $order): JsonResponse
    {
        try {
            $redirect = $this->paymentService->initiateForOrder($request->user(), $order->id, $request->input('next_order_uuid') ?: null);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'redirect' => $redirect]);
    }

    /**
     * POST /orders/{order}/pay-remaining
     * Opens (or resumes) the Safepay checkout session for a restocked
     * preorder's remaining balance. The amount is computed server-side.
     */
    public function initiateRemaining(RemainingPaymentRequest $request, Order $order): JsonResponse
    {
        try {
            $redirect = $this->paymentService->initiateRemaining($request->user(), $order->id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success'   => true,
            'redirect'  => $redirect,
            'remaining' => RemainingPaymentDTO::fromOrder($order->refresh())->toArray(),
        ]);
    }

    /**
     * GET|POST /payments/safepay/return/{token}
     * Both Safepay success_url and cancel_url point here. The browser
     * landing on this page is never treated as proof of anything — it
     * only triggers a fresh, authoritative server-side verification.
     * {token} is an opaque one-time token issued by PaymentService (no
     * order id/uuid appears in the Safepay URL); it resolves, via the
     * customer's own session, to the order and any chained sibling order.
     */
    public function return(Request $request, string $token): RedirectResponse
    {
        $attempt = $this->paymentService->resolveReturn($token);
        $order   = $attempt ? Order::find($attempt['order_id']) : null;

        if (! $order) {
            // Opened from another browser/session, or the token is gone. The
            // webhook confirms the payment regardless; just send them to
            // their orders rather than guessing.
            return redirect()->route('profile', ['tab' => 'orders'])
                ->with('status', 'If you just paid, your order will update shortly.');
        }

        abort_unless($order->user_id === $request->user()?->id, 404);

        try {
            $order = $this->paymentService->reconcileOrder($order);
        } catch (\Throwable $e) {
            Log::error('Safepay return verification failed.', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return redirect()->route('orders.show', $order)
                ->with('error', 'We could not confirm your payment yet. Please check back shortly.');
        }

        $succeeded = in_array($order->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_PARTIALLY_PAID], true);
        $nextUuid = $attempt['next'] ?? null;

        // Mixed-cart checkout (normal + preorder): once this order's
        // payment succeeds, chain straight into paying the sibling order
        // instead of stopping at a confirmation page. `next` is a uuid kept
        // server-side in the session and is only ever resolved against the
        // user's own orders.
        $nextOrderId = $succeeded && is_string($nextUuid) && Str::isUuid($nextUuid)
            ? $request->user()->orders()->where('uuid', $nextUuid)->value('id')
            : null;

        if ($nextOrderId) {
            try {
                $redirect = $this->paymentService->initiateForOrder($request->user(), $nextOrderId);

                return redirect()->away($redirect);
            } catch (\RuntimeException $e) {
                Log::info('Could not chain into next order payment.', ['next_order_id' => $nextOrderId, 'message' => $e->getMessage()]);
                // Fall through to showing the first order's own confirmation.
            }
        }

        return match (true) {
            $order->payment_status === Order::PAYMENT_PAID && $order->payment_stage === Order::STAGE_COMPLETED
                => redirect()->route('orders.show', $order)->with('status', 'Remaining payment received — your order is confirmed.'),
            $order->payment_status === Order::PAYMENT_PAID
                => redirect()->route('orders.show', $order)->with('status', 'Payment received — your order is confirmed.'),
            $order->payment_status === Order::PAYMENT_PARTIALLY_PAID
                => redirect()->route('orders.show', $order)->with('status', 'Advance payment received — your preorder is confirmed.'),
            $order->payment_status === Order::PAYMENT_CANCELLED
                => redirect()->route('orders.show', $order)->with('error', 'Payment was cancelled.'),
            $order->payment_status === Order::PAYMENT_FAILED
                => redirect()->route('orders.show', $order)->with('error', 'Payment failed. Please try again.'),
            default
                => redirect()->route('orders.show', $order)->with('status', 'Your payment is still being confirmed — this page will update shortly.'),
        };
    }

    /**
     * POST /payments/safepay/webhook
     * CSRF-exempt (see VerifyCsrfToken::$except) and authenticated purely
     * by the X-SFPY-SIGNATURE HMAC, not by a Laravel session. This is the
     * primary, authoritative confirmation path — once the signature is
     * verified, the payload's own state/amount are trusted directly (see
     * PaymentService::applyWebhookEvent).
     */
    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $raw       = $request->getContent();
        $signature = $request->header('X-SFPY-SIGNATURE');

        if (! $this->paymentService->verifyWebhookSignature($raw, $signature)) {
            // TEMPORARY diagnostic logging while tracking down a real
            // signature mismatch — never logs the secret itself, only the
            // public payload/signature, so this is safe to leave briefly.
            Log::warning('Rejected Safepay webhook with an invalid signature.', [
                'signature_header' => $signature,
                'raw_body'         => $raw,
            ]);

            return response('invalid signature', 400);
        }

        $payload = json_decode($raw, true) ?: [];

        try {
            $this->paymentService->applyWebhookEvent($payload);
        } catch (\Throwable $e) {
            Log::error('Safepay webhook processing failed.', ['type' => $payload['type'] ?? null, 'message' => $e->getMessage()]);

            // Acknowledge receipt anyway — Safepay will keep retrying an
            // endpoint that doesn't return 200, and a duplicate delivery
            // is handled idempotently if it succeeds on retry.
        }

        return response('ok', 200);
    }
}
