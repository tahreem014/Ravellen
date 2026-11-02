{{-- <option>s for a country <select>: Pakistan first (our home market), then every country A–Z. The option value is the country's name; data-code is what the city list is fetched by. --}}
@props(['selected' => null, 'placeholder' => null, 'pin' => ['PK']])
@php
    $locations = app(\App\Services\LocationService::class);
    $current   = $locations->findCountry($selected)['name'] ?? null;
    $countries = collect($locations->countries());
    $pinned    = $countries->filter(fn ($country) => in_array($country['code'], $pin, true));
    $others    = $countries->reject(fn ($country) => in_array($country['code'], $pin, true));
@endphp
@if ($placeholder)
    <option value="" @selected($current === null)>{{ $placeholder }}</option>
@endif
@foreach ($pinned as $country)
    <option value="{{ $country['name'] }}" data-code="{{ $country['code'] }}" @selected($current === $country['name'])>{{ $country['name'] }}</option>
@endforeach
@if ($pinned->isNotEmpty())
    <option disabled>──────────</option>
@endif
@foreach ($others as $country)
    <option value="{{ $country['name'] }}" data-code="{{ $country['code'] }}" @selected($current === $country['name'])>{{ $country['name'] }}</option>
@endforeach
