@extends('layouts.landing-page-layout')
@section('title', $name)

@section('content')
@php
    [$accent, $accent2] = match (true) {
        str_starts_with($name, 'BTS') => ['#7c3aed', '#a855f7'],
        $name === 'Ceramic Items'     => ['#8B5E3C', '#C8A98A'],
        default                       => ['#ec4899', '#f472b6'],
    };
@endphp
{{-- A category with no products: shown instead of its product page. The live attributes reload it once a product is added. --}}
<div class="pb-5 rv-pt-back" style="min-height:70vh;"
     data-live-listing="category:{{ $name }}"
     data-live-signature="{{ app(\App\Services\LiveUpdateService::class)->listingSignature('category:' . $name) }}">
    <div class="container px-3 px-sm-4 pt-5">
        <x-coming-soon :title="'We\'re getting ' . $name . ' ready'"
                       message="Check back soon!"
                       :accent="$accent" :accent2="$accent2" />
    </div>
</div>
@endsection
