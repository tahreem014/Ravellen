<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Notifications store their link as a literal URL in the data JSON, so
     * the ones created before orders moved to uuid route keys still point
     * at /orders/{numeric id}, which no longer resolves. Rewrite them.
     */
    public function up()
    {
        // The JSON is stored with escaped slashes (http:\/\/…\/orders\/85),
        // so match on the bare word and let the regex below do the real work.
        DB::table('notifications')
            ->where('data', 'like', '%orders%')
            ->orderBy('id')
            ->each(function ($notification) {
                $data = json_decode($notification->data, true);
                $url  = $data['action_url'] ?? null;

                if (! is_string($url) || ! preg_match('#^(.*/orders/)(\d+)$#', $url, $m)) {
                    return;
                }

                $uuid = DB::table('orders')->where('id', (int) $m[2])->value('uuid');

                if (! $uuid) {
                    return; // order no longer exists; nothing to point at
                }

                $data['action_url'] = $m[1] . $uuid;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['data' => json_encode($data)]);
            });
    }

    public function down()
    {
        // Intentionally a no-op: the numeric links no longer resolve.
    }
};
