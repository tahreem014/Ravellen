<?php

namespace App\Rules;

use App\Services\LocationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** The value must be a real country from the bundled list (any letter case, or its ISO code). */
class KnownCountry implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(LocationService::class)->findCountry((string) $value)) {
            $fail('Please choose a country from the list.');
        }
    }
}
