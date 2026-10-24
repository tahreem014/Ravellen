<?php

namespace App\Services\Payment;

use App\DataTransferObjects\Payment\PaymentInitiationDTO;
use App\DataTransferObjects\Payment\SafepayPaymentResultDTO;
use App\Models\Order;
use App\Models\User;
use App\Services\Safepay\SafepayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    private const CURRENCY = 'PKR';

    public function __construct(private readonly SafepayService $safepay)
    {
    }

    /**
     * Start (or resume) the order's first charge — full payment for a
     * normal order, or the advance for a preorder. Returns the Safepay
     * hosted checkout URL to redirect the customer to.
     *
     * @param  string|null  $nextOrderUuid  A sibling order's uuid (from the same
     *         mixed-cart checkout) to automatically continue to once this
     *         payment succeeds. Purely a UX hint — re-validated when it's
     *         actually used, never trusted for authorization or amount.
     * @throws \RuntimeException if the order doesn't belong to the user or isn't payable right now
     */
    public function initiateForOrder(User $user, int $orderId, ?string $nextOrderUuid = null): string
    {
        return DB::transaction(function () use ($user, $orderId, $nextOrderUuid) {
            $order = $user->orders()->lockForUpdate()->findOrFail($orderId);

            if (! $order->isAwaitingInitialPayment()) {
                throw new \RuntimeException('This order is not awaiting payment.');
            }

            $amount = $order->initialPaymentAmount();

            if ($amount <= 0) {
                throw new \RuntimeException('Nothing to pay for this order.');
            }

            $returnUrl = $this->rememberReturn($order, $nextOrderUuid);

            // Resume the same in-flight tracker instead of opening a
            // duplicate one (e.g. a double click or two open tabs).
            if ($order->payment_reference && $order->payment_status === Order::PAYMENT_PENDING) {
                return $this->safepay->buildCheckoutUrl($order->payment_reference, $this->gatewayReference($order), $returnUrl, $returnUrl);
            }

            $session = $this->safepay->createCheckoutSession(new PaymentInitiationDTO(
                orderId:        $order->id,
                amount:         $amount,
                currency:       self::CURRENCY,
                orderReference: $this->gatewayReference($order),
                successUrl:     $returnUrl,
                cancelUrl:      $returnUrl,
            ));

            $order->update(['payment_reference' => $session['tracker']]);

            return $session['redirect'];
        });
    }

    /**
     * Start (or resume) the remaining-balance charge on a restocked
     * preorder. Returns the Safepay hosted checkout URL.
     *
     * @throws \RuntimeException if the order doesn't belong to the user or has no remaining payment due
     */
    public function initiateRemaining(User $user, int $orderId): string
    {
        return DB::transaction(function () use ($user, $orderId) {
            $order = $user->orders()->lockForUpdate()->findOrFail($orderId);

            if (! $order->isAwaitingRemainingPayment()) {
                throw new \RuntimeException('This order has no remaining payment due right now.');
            }

            $amount = (float) $order->remaining_amount;

            if ($amount <= 0) {
                throw new \RuntimeException('Nothing remaining to pay for this order.');
            }

            $returnUrl = $this->rememberReturn($order);

            $notFailedOrCancelled = ! in_array($order->payment_status, [Order::PAYMENT_FAILED, Order::PAYMENT_CANCELLED], true);

            if ($order->remaining_payment_reference && $notFailedOrCancelled) {
                return $this->safepay->buildCheckoutUrl($order->remaining_payment_reference, $this->gatewayReference($order, 'remaining'), $returnUrl, $returnUrl);
            }

            $session = $this->safepay->createCheckoutSession(new PaymentInitiationDTO(
                orderId:        $order->id,
                amount:         $amount,
                currency:       self::CURRENCY,
                orderReference: $this->gatewayReference($order, 'remaining'),
                successUrl:     $returnUrl,
                cancelUrl:      $returnUrl,
            ));

            $order->update(['remaining_payment_reference' => $session['tracker']]);

            return $session['redirect'];
        });
    }

    /**
     * Resolve the opaque token from a Safepay return URL back to the
     * attempt it was issued for: ['order_id' => int, 'next' => ?string].
     * Null for an unknown token (other browser/session, or expired) — the
     * webhook still confirms the payment either way.
     */
    public function resolveReturn(string $token): ?array
    {
        $attempt = session("safepay_returns.{$token}");

        return is_array($attempt) ? $attempt : null;
    }

    /**
     * The success/cancel URL handed to Safepay. It carries only a random
     * one-time token — no order id, uuid or sibling-order uuid ever appears
     * in the Safepay URL. Which order (and chained order) the token stands
     * for is kept in the customer's own session, so the token is useless
     * to anyone else. Only the latest few are kept: the session is a
     * cookie, which has a size limit.
     */
    private function rememberReturn(Order $order, ?string $nextOrderUuid = null): string
    {
        $token = Str::random(32);

        $returns = array_slice(
            [...session('safepay_returns', []), $token => ['order_id' => $order->id, 'next' => $nextOrderUuid]],
            -5,
            null,
            true,
        );

        session(['safepay_returns' => $returns]);

        return route('payments.safepay.return', ['token' => $token]);
    }

    /**
     * The order reference shown to (and stored by) Safepay. A keyed hash of
     * the order's uuid, so it is stable across resumed attempts but reveals
     * nothing about the order and can't be reversed. The Safepay tracker
     * (payment_reference), not this value, is what matches a payment back
     * to an order.
     */
    private function gatewayReference(Order $order, string $phase = 'initial'): string
    {
        return 'rv_' . substr(hash_hmac('sha256', "{$order->uuid}:{$phase}", (string) config('app.key')), 0, 24);
    }

    /**
     * Best-effort reconciliation via Fetch Tracker, used by the customer's
     * return-redirect landing (which has no webhook payload to work from,
     * only a tracker id). Still never trusts the redirect itself — it
     * re-verifies with Safepay — but it's secondary to applyWebhookEvent()
     * below, which is the primary, reliable confirmation path.
     */
    public function reconcile(string $tracker): ?Order
    {
        $order = Order::where('payment_reference', $tracker)
            ->orWhere('remaining_payment_reference', $tracker)
            ->first();

        if (! $order) {
            Log::info('Safepay reconcile: no order matches this tracker.', ['tracker' => $tracker]);

            return null;
        }

        if ($this->isAlreadyFinalized($order, $tracker)) {
            return $order;
        }

        $result = $this->safepay->fetchTracker($tracker);

        return $this->finalize($order, $tracker, $result);
    }

    /**
     * Authoritative, idempotent reconciliation from a webhook event whose
     * HMAC signature has already been verified by the caller. This is the
     * primary confirmation path: the verified payload's own state/amount
     * are trusted directly, rather than requiring a second Fetch Tracker
     * round-trip (which Safepay's sandbox does not reliably support for
     * every checkout flow). Safe to call repeatedly for the same tracker.
     */
    public function applyWebhookEvent(array $payload): ?Order
    {
        $data = $payload['data'] ?? [];
        // Confirmed from a real delivery: the actual tracker/state/amount
        // live under data.notification, not flat on data as documented.
        $tracker = $data['notification']['tracker'] ?? $data['tracker'] ?? null;

        if (! is_string($tracker) || $tracker === '') {
            Log::info('Safepay webhook payload had no tracker.', ['payload' => $payload]);

            return null;
        }

        $order = Order::where('payment_reference', $tracker)
            ->orWhere('remaining_payment_reference', $tracker)
            ->first();

        if (! $order) {
            Log::info('Safepay webhook: no order matches this tracker.', ['tracker' => $tracker]);

            return null;
        }

        if ($this->isAlreadyFinalized($order, $tracker)) {
            return $order;
        }

        $result = SafepayPaymentResultDTO::fromWebhookData($data);

        return $this->finalize($order, $tracker, $result);
    }

    private function finalize(Order $order, string $tracker, SafepayPaymentResultDTO $result): Order
    {
        return DB::transaction(function () use ($order, $tracker, $result) {
            /** @var Order $locked */
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $locked || $this->isAlreadyFinalized($locked, $tracker)) {
                return $locked;
            }

            $this->applyResult($locked, $tracker, $result);

            return $locked->refresh();
        });
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        return $this->safepay->verifyWebhookSignature($rawBody, $signature);
    }

    /**
     * Reconcile whichever Safepay tracker is currently relevant for this
     * order (the remaining-balance one once restocked, otherwise the
     * initial charge). Used by the return-redirect handler, which only
     * knows the order, not which tracker to check.
     */
    public function reconcileOrder(Order $order): Order
    {
        $tracker = $order->order_status === Order::STATUS_PAYMENT_DUE && $order->remaining_payment_reference
            ? $order->remaining_payment_reference
            : $order->payment_reference;

        if (! $tracker) {
            return $order;
        }

        return $this->reconcile($tracker) ?? $order;
    }

    private function isAlreadyFinalized(Order $order, string $tracker): bool
    {
        $isRemainingPhase = $order->remaining_payment_reference === $tracker;

        if ($isRemainingPhase) {
            return $order->payment_stage === Order::STAGE_COMPLETED;
        }

        if ($order->isPreOrder()) {
            return in_array($order->payment_stage, [Order::STAGE_ADVANCE_PAID, Order::STAGE_COMPLETED], true);
        }

        return $order->payment_status === Order::PAYMENT_PAID;
    }

    private function applyResult(Order $order, string $tracker, SafepayPaymentResultDTO $result): void
    {
        $isRemainingPhase = $order->remaining_payment_reference === $tracker;

        if ($result->isPaid()) {
            $this->applyPaid($order, $tracker, $result, $isRemainingPhase);

            return;
        }

        if ($result->isCancelled()) {
            $order->update(['payment_status' => Order::PAYMENT_CANCELLED]);

            return;
        }

        if ($result->isTerminallyFailed()) {
            $order->update(['payment_status' => Order::PAYMENT_FAILED]);

            return;
        }

        // Still in progress (TRACKER_STARTED / AUTHORIZED / ENROLLED) — nothing to do yet.
    }

    private function applyPaid(Order $order, string $tracker, SafepayPaymentResultDTO $result, bool $isRemainingPhase): void
    {
        $expected = $isRemainingPhase ? (float) $order->remaining_amount : $order->initialPaymentAmount();

        if ($result->amount !== null && ! $this->amountMatches($result->amount, $expected)) {
            Log::warning('Safepay paid amount does not match the expected amount — not marking as paid.', [
                'order_id' => $order->id,
                'tracker'  => $tracker,
                'reported' => $result->amount,
                'expected' => $expected,
            ]);

            return;
        }

        if ($isRemainingPhase) {
            $order->update([
                'payment_stage'     => Order::STAGE_COMPLETED,
                'payment_status'    => Order::PAYMENT_PAID,
                'remaining_paid_at' => now(),
                'order_status'      => Order::STATUS_CONFIRMED,
            ]);

            return;
        }

        if ($order->isPreOrder()) {
            $order->update([
                'payment_status' => Order::PAYMENT_PARTIALLY_PAID,
                'payment_stage'  => Order::STAGE_ADVANCE_PAID,
                'order_status'   => Order::STATUS_AWAITING_STOCK,
                'paid_at'        => now(),
            ]);

            return;
        }

        $order->update([
            'payment_status' => Order::PAYMENT_PAID,
            'order_status'   => Order::STATUS_CONFIRMED,
            'paid_at'        => now(),
        ]);
    }

    /**
     * Safepay's Fetch Tracker response has been observed to report amounts
     * in the minor currency unit (paisa) while this app charges in whole
     * rupees — accept either so an unconfirmed unit doesn't block a
     * legitimately matching payment, while still rejecting genuinely
     * different amounts.
     */
    private function amountMatches(float $reported, float $expectedRupees): bool
    {
        $tolerance = 1.0;

        if (abs($reported - $expectedRupees) <= $tolerance) {
            return true;
        }

        return abs($reported - ($expectedRupees * 100)) <= ($tolerance * 100);
    }
}
