<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'support_email',
        'support_phone',
        'preorder_advance_rate',
    ];

    public function edit()
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn ($key) => [$key => Setting::get($key)]);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'support_email' => ['required', 'email'],
            'support_phone' => ['required', 'string', 'max:255'],
            'preorder_advance_rate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('status', 'Settings updated.');
    }
}
