<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The footer, the contact page and the policy pages all read the support email and phone saved in
 * Admin → Settings, and the contact form mails its messages to that email. Move them from the
 * placeholder values the settings table was seeded with to the shop's real ones. A value an admin
 * has already changed is left alone.
 */
return new class extends Migration
{
    private array $details = [
        'support_email' => ['info@ravellen.com',  'ravellenraynae@gmail.com'],
        'support_phone' => ['+92-300-1234567',    '+92 343 5587925'],
    ];

    public function up(): void
    {
        foreach ($this->details as $key => [$placeholder, $real]) {
            DB::table('settings')->where('key', $key)->where('value', $placeholder)->update(['value' => $real]);
        }
    }

    public function down(): void
    {
        foreach ($this->details as $key => [$placeholder, $real]) {
            DB::table('settings')->where('key', $key)->where('value', $real)->update(['value' => $placeholder]);
        }
    }
};
