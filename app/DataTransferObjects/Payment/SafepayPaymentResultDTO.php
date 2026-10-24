<?php

namespace App\DataTransferObjects\Payment;

/**
 * Normalized result of either a Safepay "Fetch Tracker" verification call
 * or a webhook delivery. These two sources use different state
 * vocabularies, confirmed empirically against this merchant's live
 * sandbox (not from docs, which didn't match reality):
 *
 * - Fetch Tracker: TRACKER_ENDED / TRACKER_CANCELLED / TRACKER_EXPIRED /
 *   TRACKER_VOIDED / TRACKER_REVERSED / ... (per Safepay's published docs).
 * - Webhook delivery: a flat "PAID" (confirmed from a real payload) for
 *   success. Failure/cancellation state strings for this vocabulary are
 *   not yet confirmed from a real delivery — see isCancelled()/
 *   isTerminallyFailed() below.
 */
final readonly class SafepayPaymentResultDTO
{
    public function __construct(
        public string  $tracker,
        public ?string $state,
        public ?float  $amount,
        public ?string $currency,
        public array   $raw,
    ) {}

    public static function fromTrackerResponse(string $tracker, array $data): self
    {
        // GET /order/v1/{tracker} reports amount/currency flat on the
        // tracker (major units, e.g. 6000 = PKR 6,000.00); the nested
        // shapes below are from the reporter API's payload.
        $amount = $data['amount']
            ?? $data['charge']['amount']['amount']
            ?? $data['purchase_totals']['base_amount']['amount']
            ?? null;

        $currency = $data['currency']
            ?? $data['charge']['amount']['currency']
            ?? $data['purchase_totals']['base_amount']['currency']
            ?? null;

        return new self(
            tracker:  $tracker,
            state:    $data['state'] ?? null,
            amount:   $amount !== null ? (float) $amount : null,
            currency: $currency,
            raw:      $data,
        );
    }

    /**
     * From a real webhook delivery's top-level `data` object. Confirmed
     * shape from an actual payload (not the published docs, which
     * described something flatter and didn't match):
     *
     *   data: {
     *     type: "payment:created",
     *     notification: {
     *       tracker, state, amount, currency, fee, net, user,
     *       metadata: { order_id, source }
     *     },
     *     resource, delivery_attempts, created_at, ...
     *   }
     *
     * The actual tracker/state/amount live under `data.notification`,
     * three levels deeper than documented.
     */
    public static function fromWebhookData(array $data): self
    {
        $notification = $data['notification'] ?? $data;

        return new self(
            tracker:  $notification['tracker'] ?? '',
            state:    $notification['state'] ?? null,
            amount:   isset($notification['amount']) ? (float) $notification['amount'] : null,
            currency: $notification['currency'] ?? null,
            raw:      $data,
        );
    }

    public function isPaid(): bool
    {
        return in_array($this->state, ['TRACKER_ENDED', 'PAID'], true);
    }

    public function isCancelled(): bool
    {
        // 'CANCELLED' is an educated guess for the webhook vocabulary,
        // not yet confirmed from a real payload (only 'PAID' has been
        // observed so far) — safe either way, since an unrecognized
        // state just leaves the order unchanged rather than misfiring.
        return in_array($this->state, ['TRACKER_CANCELLED', 'CANCELLED'], true);
    }

    /** Terminal, unpaid, and not recoverable — a fresh attempt is required. */
    public function isTerminallyFailed(): bool
    {
        // Same caveat as isCancelled(): the non-TRACKER_* values here are
        // unconfirmed guesses for the webhook vocabulary.
        return in_array($this->state, [
            'TRACKER_EXPIRED', 'TRACKER_VOIDED', 'TRACKER_REVERSED',
            'FAILED', 'DECLINED', 'EXPIRED', 'VOIDED',
        ], true);
    }
}
