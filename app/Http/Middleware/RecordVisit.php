<?php

namespace App\Http\Middleware;

use App\Models\SiteEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Counts storefront visits for the admin dashboard. A visit starts with the
 * first real page a browser loads and lasts until it has been idle for
 * VISIT_MINUTES; the visit id lives in a cookie that every further page
 * refreshes. Only successful HTML pages from ordinary shoppers count — not
 * admin pages, the admins themselves, crawlers, prefetches or the background
 * checks the pages make (GET /live, X-Live-Refresh).
 */
class RecordVisit
{
    public const COOKIE = 'rv_visit';

    private const VISIT_MINUTES = 30;

    private const BOTS = '/bot|crawl|spider|slurp|preview|monitor|headless|lighthouse|curl|wget|python|java|go-http|scrapy/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->isPageView($request, $response)) {
            $this->touchVisit($request);
        }

        return $response;
    }

    /** Whether $value is a visit id we issued (the cookie is encrypted, but never trust the shape). */
    public static function isVisitId(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9]{32}$/', $value) === 1;
    }

    private function isPageView(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return false;
        }

        if ($request->is('admin', 'admin/*') || $request->headers->has('X-Live-Refresh')) {
            return false;
        }

        if ($request->headers->get('Purpose') === 'prefetch' || str_starts_with((string) $request->headers->get('Sec-Purpose'), 'prefetch')) {
            return false;
        }

        $agent = (string) $request->userAgent();
        if ($agent === '' || preg_match(self::BOTS, $agent)) {
            return false;
        }

        return ! $request->user()?->isAdmin();
    }

    private function touchVisit(Request $request): void
    {
        try {
            $visitId = $request->cookie(self::COOKIE);

            if (! self::isVisitId($visitId)) {
                $visitId = Str::random(32);
                SiteEvent::create(['type' => SiteEvent::VISIT, 'visit_id' => $visitId]);
            }

            Cookie::queue(self::COOKIE, $visitId, self::VISIT_MINUTES);
        } catch (Throwable $e) {
            // Analytics must never break a page.
            report($e);
        }
    }
}
