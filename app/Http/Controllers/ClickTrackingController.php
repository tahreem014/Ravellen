<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RecordVisit;
use App\Models\SiteEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClickTrackingController extends Controller
{
    /**
     * POST /track/click
     * Beaconed by layouts/track-clicks whenever a shopper presses a link or
     * button. Counted only inside a visit RecordVisit started, which leaves
     * out crawlers, admins and anything without the visit cookie.
     */
    public function __invoke(Request $request): Response
    {
        $visitId = $request->cookie(RecordVisit::COOKIE);

        if (RecordVisit::isVisitId($visitId)) {
            SiteEvent::create(['type' => SiteEvent::CLICK, 'visit_id' => $visitId]);
        }

        return response()->noContent();
    }
}
