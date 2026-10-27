@props(['title' => 'Coming Soon', 'message' => null, 'icon' => 'ri-cup-line', 'accent' => '#8B5E3C', 'accent2' => '#C8A98A', 'actions' => true])
{{--
    "Coming soon" panel for a category with nothing on sale yet (Category::COMING_SOON).
    The heading carries .coming-soon-title so a page can retitle it from script.
--}}
<div {{ $attributes->merge(['class' => 'coming-soon text-center mx-auto']) }} style="max-width:34rem;padding:2.5rem 1.25rem;">
    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
         style="width:4.5rem;height:4.5rem;border-radius:50%;color:#fff;font-size:2rem;background:linear-gradient(135deg,{{ $accent2 }},{{ $accent }});box-shadow:0 8px 24px rgba(139,94,60,.25);">
        <i class="{{ $icon }}"></i>
    </div>
    <span class="d-inline-block mb-3"
          style="background:#fff;color:{{ $accent }};border:1.5px solid {{ $accent2 }};border-radius:2rem;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:.3rem .9rem;">Coming Soon</span>
    <h3 class="coming-soon-title fw-bold mb-2" style="color:#5c3d1e;font-size:clamp(1.4rem,4vw,1.9rem);">{{ $title }}</h3>
    @if($message)
        <p class="text-muted mb-4" style="font-size:.95rem;">{{ $message }}</p>
    @endif
    @if($actions)
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ url('/shop') }}" class="btn fw-semibold text-white px-4" style="background:linear-gradient(135deg,{{ $accent2 }},{{ $accent }});border-radius:.5rem;">Browse the Shop</a>
            <a href="{{ url('/') }}" class="btn fw-semibold px-4" style="background:#fff;color:{{ $accent }};border:1.5px solid {{ $accent2 }};border-radius:.5rem;">Back to Home</a>
        </div>
    @endif
</div>
