<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The storefront's background requests (GET /live every few seconds, the
 * order-section refreshes marked X-Live-Refresh, and the POST /track/click
 * beacons) change nothing the shopper depends on, so they must not hand back
 * session cookies. Sessions here live in a cookie: a check
 * that started just before a login, logout or form submit and finished just
 * after would otherwise put the older session back in the browser — logging
 * people out or failing their next form with "419 Page Expired".
 *
 * Global middleware, so it runs after the web group has added its cookies.
 */
class KeepLiveRequestsStateless
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('live', 'track/click') || $request->headers->has('X-Live-Refresh')) {
            foreach ($response->headers->getCookies() as $cookie) {
                $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
            }
        }

        return $response;
    }
}
