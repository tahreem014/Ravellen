<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin forms marked data-ajax are saved in the background (see the admin
 * layout). Their controllers still answer the usual way — redirect back with
 * a 'status' or 'error' message, or with errors — so for those background
 * requests this turns that redirect into JSON the page can show as a toast,
 * and keeps the message from popping up again on the next page load.
 */
class RespondToBackgroundForms
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->expectsJson() || ! $response instanceof RedirectResponse) {
            return $response;
        }

        $session = $request->session();
        $errors  = $session->get('errors');
        $status  = $session->get('status');
        $error   = $session->get('error');
        $session->forget(['errors', 'status', 'error', '_old_input']);

        if ($errors || $error) {
            return new JsonResponse([
                'message' => $error ?? $errors->first(),
                'errors'  => $errors ? $errors->getBag('default')->toArray() : [],
            ], 422);
        }

        return new JsonResponse(['message' => $status]);
    }
}
