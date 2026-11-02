<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rates and addresses were typed in by hand ("pakistan", "karachi"). Countries and cities are
 * now picked from the bundled location list, so respell what already exists the same way: rates
 * and addresses then show and match exactly as the pickers offer them. Nothing changes meaning,
 * no row is added or removed, and order history is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $dir       = resource_path('data/locations');
        $countries = json_decode(file_get_contents("{$dir}/countries.json"), true) ?? [];
        $byName    = [];

        foreach ($countries as $country) {
            $byName[mb_strtolower($country['name'])] = $country;
        }

        foreach (['shipping_rates', 'addresses'] as $table) {
            foreach (DB::table($table)->whereNotNull('country')->get(['id', 'country', 'city']) as $row) {
                $country = $byName[mb_strtolower(trim($row->country))] ?? null;

                if (! $country) {
                    continue;
                }

                $update = ['country' => $country['name']];

                // Only admin-set rate cities are respelled; a customer's address city stays as they wrote it
                if ($table === 'shipping_rates' && $row->city !== null) {
                    $update['city'] = $this->listedCity($dir, $country['code'], $row->city) ?? $row->city;
                }

                if ($update['country'] !== $row->country || ($update['city'] ?? $row->city) !== $row->city) {
                    DB::table($table)->where('id', $row->id)->update($update);
                }
            }
        }
    }

    public function down(): void
    {
        // Only spelling changed; there is nothing to restore.
    }

    private function listedCity(string $dir, string $code, string $city): ?string
    {
        $path = "{$dir}/cities/{$code}.json";

        if (! is_file($path)) {
            return null;
        }

        foreach (json_decode(file_get_contents($path), true) ?? [] as $name) {
            if (mb_strtolower($name) === mb_strtolower(trim($city))) {
                return $name;
            }
        }

        return null;
    }
};
