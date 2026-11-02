<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Rebuilds the bundled country/city lists in resources/data/locations from GeoNames
 * (https://www.geonames.org, CC BY 4.0): every country, plus each country's cities of 5,000+
 * people. The result is committed, so the site needs no location API at runtime; run this
 * only to refresh the data, e.g. once a year.
 */
class SyncLocations extends Command
{
    protected $signature = 'locations:sync';

    protected $description = 'Refresh the bundled country and city lists from GeoNames';

    private const BASE_URL = 'https://download.geonames.org/export/dump/';

    /** Historical, abandoned, destroyed, and "section of a populated place" entries are not cities. */
    private const SKIPPED_FEATURE_CODES = ['PPLX', 'PPLH', 'PPLQ', 'PPLW', 'PPLCH'];

    public function handle(): int
    {
        $work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ravellen-geonames-' . Str::random(8);
        File::makeDirectory($work);

        try {
            $countries = $this->countries($this->download('countryInfo.txt', $work));
            $cities    = $this->cities($this->extractCities($this->download('cities5000.zip', $work)), array_keys($countries));
        } finally {
            File::deleteDirectory($work);
        }

        // Defunct (Netherlands Antilles) and uninhabited (Antarctica, Bouvet…) entries have no
        // city at all, and nothing can be delivered to them
        $countries = array_intersect_key($countries, $cities);

        $target = resource_path('data/locations');
        File::deleteDirectory($target);
        File::makeDirectory($target . DIRECTORY_SEPARATOR . 'cities', 0755, true);

        $rows = collect($countries)->sortBy(fn ($name) => Str::ascii($name))
            ->map(fn ($name, $code) => json_encode(['code' => $code, 'name' => $name], JSON_UNESCAPED_UNICODE));
        File::put($target . '/countries.json', "[\n" . $rows->implode(",\n") . "\n]\n");

        foreach ($cities as $code => $names) {
            File::put("{$target}/cities/{$code}.json", json_encode($names, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $this->info(count($countries) . ' countries, ' . array_sum(array_map('count', $cities)) . ' cities in ' . count($cities) . ' countries.');

        return self::SUCCESS;
    }

    private function download(string $file, string $dir): string
    {
        $path     = $dir . DIRECTORY_SEPARATOR . $file;
        $response = Http::timeout(120)->sink($path)->get(self::BASE_URL . $file);

        if (! $response->successful()) {
            throw new \RuntimeException("Could not download {$file} (HTTP {$response->status()}).");
        }

        return $path;
    }

    /** @return array<string, string> ISO code => country name */
    private function countries(string $path): array
    {
        $countries = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with($line, '#')) {
                continue;
            }

            $columns = explode("\t", $line);

            // 0 = ISO alpha-2, 4 = country name
            if (strlen($columns[0] ?? '') === 2 && ($columns[4] ?? '') !== '') {
                $countries[$columns[0]] = $columns[4];
            }
        }

        return $countries;
    }

    /** Reads the one entry we need straight out of the archive; nothing else is ever extracted. */
    private function extractCities(string $zipPath): string
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true || ($contents = $zip->getFromName('cities5000.txt')) === false) {
            throw new \RuntimeException('cities5000.zip is not the archive we expected.');
        }

        $zip->close();

        $path = dirname($zipPath) . DIRECTORY_SEPARATOR . 'cities5000.txt';
        File::put($path, $contents);

        return $path;
    }

    /**
     * @param  string[]  $countryCodes
     * @return array<string, string[]> ISO code => sorted, de-duplicated city names
     */
    private function cities(string $path, array $countryCodes): array
    {
        $known  = array_flip($countryCodes);
        $byCode = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $columns = explode("\t", $line);
            $code    = $columns[8] ?? '';
            $name    = trim($columns[1] ?? '');

            if ($name === '' || ! isset($known[$code]) || in_array($columns[7] ?? '', self::SKIPPED_FEATURE_CODES, true)) {
                continue;
            }

            // "Bāgh" and "Bagh" are one place: keep a single spelling, preferring the plain one
            $key      = mb_strtolower(Str::ascii($name));
            $existing = $byCode[$code][$key] ?? null;

            if ($existing === null || (! $this->isPlain($existing) && $this->isPlain($name))) {
                $byCode[$code][$key] = $name;
            }
        }

        return collect($byCode)
            ->map(fn (array $names) => collect($names)->sortBy(fn ($name) => Str::ascii($name), SORT_NATURAL | SORT_FLAG_CASE)->values()->all())
            ->sortKeys()
            ->all();
    }

    private function isPlain(string $name): bool
    {
        return Str::ascii($name) === $name;
    }
}
