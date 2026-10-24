<?php

namespace App\Services\Safepay;

use App\DataTransferObjects\Payment\PaymentInitiationDTO;
use App\DataTransferObjects\Payment\SafepayPaymentResultDTO;
use App\Exceptions\SafepayException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Safepay\Checkout as SafepayCheckout;
use Safepay\Verify as SafepayVerify;

/**
 * Thin wrapper around Safepay's API. Order initiation and Fetch Tracker
 * verification are called directly via Laravel's HTTP client rather than
 * through the official getsafepay/safepay-php SDK's Payments/Requestor
 * classes, which have a URL-concatenation bug (see initOrder()) and,
 * via the top-level Safepay facade, insist on a webhookSecret and
 * v1Secret being present just to charge a card. Checkout URL building
 * (no network call) still uses the SDK's own Checkout class. Never logs
 * the secret key; never used by anything client-facing.
 */
class SafepayService
{
    private ?SafepayCheckout $checkout = null;

    public function __construct(private readonly array $config)
    {
    }

    /**
     * Open a new hosted checkout session for the given amount.
     * Returns ['tracker' => string, 'redirect' => string].
     */
    public function createCheckoutSession(PaymentInitiationDTO $dto): array
    {
        $this->requirePublicKey();

        $tracker = $this->initOrder($dto->amount, $dto->currency);

        if (! is_string($tracker) || $tracker === '') {
            Log::error('Safepay did not return a payment token.', ['order_reference' => $dto->orderReference]);

            throw new SafepayException('Unable to start the Safepay payment session.');
        }

        return [
            'tracker'  => $tracker,
            'redirect' => $this->buildCheckoutUrl($tracker, $dto->orderReference, $dto->successUrl, $dto->cancelUrl),
        ];
    }

    /**
     * Rebuild the hosted checkout URL for a tracker that was already
     * created (no new charge/token request) — used to resume an
     * in-flight attempt instead of opening a duplicate one.
     */
    public function buildCheckoutUrl(string $tracker, string $orderReference, string $successUrl, string $cancelUrl): string
    {
        $link = $this->checkout()->create([
            'token'       => $tracker,
            'order_id'    => $orderReference,
            'source'      => 'custom',
            'webhooks'    => 'true',
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
        ]);

        if (($link['result'] ?? null) !== 'success' || empty($link['redirect'])) {
            throw new SafepayException('Unable to build the Safepay checkout link.');
        }

        return $link['redirect'];
    }

    /**
     * Authoritative server-to-server verification of a tracker's current
     * state. This — not the browser redirect, not the webhook payload's
     * own claims — is the source of truth for whether an order got paid.
     */
    public function fetchTracker(string $tracker): SafepayPaymentResultDTO
    {
        $this->requireSecretKey();

        $base = $this->config['environment'] === 'production'
            ? 'https://api.getsafepay.com'
            : 'https://sandbox.api.getsafepay.com';

        // GET /order/v1/{tracker} is the lookup that works for trackers
        // created via POST /order/v1/init. The previously used
        // /reporter/api/v1/payments/{tracker} returned 500 "cannot find
        // tracker ... using keys" for a tracker that exists and is
        // TRACKER_ENDED. Checked against the sandbox, this endpoint
        // returns the tracker even with no/invalid merchant header, so
        // the secret key is not what authorizes it; it is still sent in
        // case production enforces it (production is untested).
        $response = Http::withHeaders([
            'X-SFPY-MERCHANT-SECRET' => $this->config['secret_key'],
        ])->get("{$base}/order/v1/{$tracker}");

        if ($response->failed()) {
            Log::error('Safepay fetch-tracker request failed.', [
                'tracker' => $tracker,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);

            throw new SafepayException('Unable to verify payment with Safepay.');
        }

        $data = $response->json('data') ?? [];

        return SafepayPaymentResultDTO::fromTrackerResponse($tracker, $data);
    }

    /**
     * Verify an incoming webhook's X-SFPY-SIGNATURE header against the raw
     * request body using the webhook shared secret (HMAC-SHA512).
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (! $signature || empty($this->config['webhook_secret'])) {
            return false;
        }

        try {
            $verify = new SafepayVerify([
                'webhookSecret' => $this->config['webhook_secret'],
            ]);

            return $verify->webhook($rawBody, $signature);
        } catch (\Throwable $e) {
            Log::warning('Safepay webhook signature verification failed.', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function requirePublicKey(): void
    {
        if (empty($this->config['public_key'])) {
            throw new SafepayException('Safepay is not configured: services.safepay.public_key is missing.');
        }
    }

    private function requireSecretKey(): void
    {
        if (empty($this->config['secret_key'])) {
            throw new SafepayException('Safepay is not configured: services.safepay.secret_key is missing.');
        }
    }

    /**
     * POST /order/v1/init directly, rather than through the SDK's
     * Payments::getToken(), which concatenates a trailing-slash base URL
     * with a leading-slash endpoint path into "...getsafepay.com//order/v1/init".
     * Safepay 301-redirects that double slash with an empty body, and the
     * SDK then crashes trying to parse the empty response as JSON — same
     * request shape the SDK sends, just without that bug.
     */
    private function initOrder(float $amount, string $currency): ?string
    {
        $base = $this->config['environment'] === 'production'
            ? 'https://api.getsafepay.com'
            : 'https://sandbox.api.getsafepay.com';

        $response = Http::asJson()->post("{$base}/order/v1/init", [
            'environment' => $this->config['environment'],
            'amount'      => $amount,
            'currency'    => $currency,
            // Confirmed against Safepay's sandbox: this "client" field
            // takes the public API key, not the secret key — the secret
            // key is rejected here ("Client with this identifier not
            // found") but is required separately for Fetch Tracker below.
            'client'      => $this->config['public_key'],
        ]);

        if ($response->failed() || ($response->json('status.message') !== 'success')) {
            Log::error('Safepay order/v1/init failed.', ['status' => $response->status(), 'body' => $response->json('status')]);

            return null;
        }

        return $response->json('data.token');
    }

    private function checkout(): SafepayCheckout
    {
        return $this->checkout ??= new SafepayCheckout([
            'environment' => $this->config['environment'],
        ]);
    }
}
