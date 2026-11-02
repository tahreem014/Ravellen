<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Storefront traffic for the admin dashboard: a 'visit' is recorded when a
 * shopper's browsing session starts (RecordVisit), a 'click' for each link or
 * button they press during it (ClickTrackingController).
 */
class SiteEvent extends Model
{
    public const VISIT = 'visit';
    public const CLICK = 'click';

    public const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'visit_id',
    ];

    public function scopeVisits(Builder $query): Builder
    {
        return $query->where('type', self::VISIT);
    }

    public function scopeClicks(Builder $query): Builder
    {
        return $query->where('type', self::CLICK);
    }

    public function scopeWithin(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
