@extends('layouts.landing-page-layout')
@section('title', 'BTS Merch')

@section('css')
<style>
    .bts-wrapper { background: #fff; min-height: 100vh; }

    /* ── Hero ── */
    .bts-hero {
        background: linear-gradient(135deg, #7c3aed 0%, #9333ea 60%, #a855f7 100%);
        color: #fff;
        padding: 4rem 0 0;
        position: relative;
    }
    .bts-badge {
        display: inline-flex; align-items: center; gap: .4rem;
        background: rgba(255,255,255,.15);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 2rem; padding: .3rem .9rem;
        font-size: .8rem; font-weight: 500; margin-bottom: 1.25rem;
    }
    .bts-badge i { color: #f9a8d4; }

    /* ── Product Cards ── */
    .bts-card {
        border: 0; border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.08);
        overflow: hidden;
        transition: transform .3s, box-shadow .3s;
        background: #fff;
    }
    .bts-card:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(0,0,0,.13); }
    .bts-card-img { width: 100%; height: 16rem; object-fit: cover; display: block; }

    .bts-explore-btn {
        border: 0; color: #fff; font-weight: 600; font-size: .85rem;
        border-radius: .5rem; padding: .65rem 1.25rem;
        white-space: nowrap;   /* "Order Mystery Scoop" stays on one line */
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        transition: opacity .2s; display: inline-flex; align-items: center; gap: .4rem;
    }
    .bts-explore-btn:hover { opacity: .85; color: #fff; }

    /* ── Bias Selector ── */
    
    .bias-title { font-size: 1.4rem; font-weight: 800; color: #7c3aed; letter-spacing: -.01em; }

    .bias-grid-row { display: flex; gap: .85rem; justify-content: center; flex-wrap: nowrap; }

    .bias-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: .45rem; padding: 1.1rem .75rem; min-height: 120px; flex: 1; max-width: 190px;
        border: 2px solid white; border-radius: .75rem;
        cursor: pointer; transition: all .22s; user-select: none; position: relative;
    }
    /* Phones: all nine members in one 3 × 3 grid instead of two rows that run off the screen */
    @media (max-width: 575.98px) {
        .bias-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .65rem; }
        .bias-grid-row { display: contents; }
        .bias-btn { max-width: none; min-height: 6rem; padding: .9rem .4rem; }
        .bias-btn .bias-name { font-size: .75rem; }
    }
    .bias-btn:hover  { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(168,85,247,.22); }
    .bias-btn.active { border-color: #7c3aed !important; box-shadow: 0 6px 20px rgba(124,58,237,.28); }

    /* per-member soft gradients */
    .bias-btn[data-bias="jungkook"] { background: linear-gradient(145deg,#e8e0f7,#ddd6f3); }
    .bias-btn[data-bias="taehyung"] { background: linear-gradient(145deg,#f5d6e8,#eec9df); }
    .bias-btn[data-bias="jimin"]    { background: linear-gradient(145deg,#fadadd,#f5c6cb); }
    .bias-btn[data-bias="namjoon"]  { background: linear-gradient(145deg,#d8d4e8,#ccc8de); }
    .bias-btn[data-bias="jin"]      { background: linear-gradient(145deg,#f5d6e8,#eec9df); }
    .bias-btn[data-bias="jhope"]    { background: linear-gradient(145deg,#e8e0f7,#ddd6f3); }
    .bias-btn[data-bias="suga"]     { background: linear-gradient(145deg,#d8d4e8,#ccc8de); }
    .bias-btn[data-bias="ot7"]      { background: linear-gradient(145deg,#c4b5fd,#a78bfa); }
    .bias-btn[data-bias="bt21"]     { background: linear-gradient(145deg,#ddd6fe,#c4b5fd); }

    /* each member's products (or "coming soon"); only the picked member's panel is shown */
    .bias-products { display: none; }
    .bias-products.show { display: block; }

    .bias-emoji { font-size: 2rem; line-height: 1; }
    .bias-name  { font-size: .8rem; font-weight: 700; color: #5b21b6; }
    
    .bias-btn.active .bias-heart { color: #fff; }
    .bias-btn[data-bias="ot7"] .bias-name,
    .bias-btn[data-bias="bt21"] .bias-name { color: #fff; }
    .bias-btn[data-bias="ot7"] .bias-heart,
    .bias-btn[data-bias="bt21"] .bias-heart { color: rgba(255,255,255,.5); }
    .bias-btn[data-bias="ot7"].active .bias-heart,
    .bias-btn[data-bias="bt21"].active .bias-heart { color: #fff; }

    /* ── Card badge ── */
    .prod-badge {
        position: absolute; top: .75rem; right: .75rem;
        font-size: .68rem; font-weight: 700; padding: .22rem .55rem;
        border-radius: .35rem; color: #fff;
    }
    .badge-army { background: #7c3aed; }
    .badge-min  { background: #ec4899; }

    .borahae-banner {
        background: linear-gradient(to right, #ede9fe, #fce7f3);
        border-radius: 3rem;
        padding: 1rem 2.5rem;
        font-size: 1.15rem; font-weight: 700;
        display: inline-block;
        background-clip: padding-box;
    }
    .borahae-banner span {
        background: linear-gradient(to right, #9333ea, #ec4899);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
@endsection

@section('content')
<div class="bts-wrapper">

    {{-- ── Hero ── --}}
    <div class="bts-hero text-center">
        <div class="container px-4 position-relative" style="z-index:1;">
            <div class="bts-badge">
                <i class="ri-heart-fill"></i> ARMY Exclusive Collection
            </div>
            <h1 class="fw-bold mb-3 text-white" style="font-size:clamp(2rem,5vw,3rem);">BTS Merch</h1>
            <p class="mb-4 mx-auto" style="max-width:34rem;color:rgba(255,255,255,.9);font-size:1.05rem;">
                Exclusive mystery scoops and balls for ARMY! Featuring all members. Borahae! 💜
            </p>
            <p style="color:rgba(255,255,255,.75);font-size:.9rem;">
                <i class="ri-sparkling-line me-1"></i> Special themed items for each member
            </p>
        </div>

        {{-- Wave --}}
        <div style="line-height:0;margin-top:3rem;margin-bottom:-2px;">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;display:block;">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H0V0Z" fill="#fff"/>
            </svg>
        </div>
    </div>

    {{-- ── Bias + Era + Dynamic Cards ── --}}
    {{-- data-live-listing: reload when BTS Merch products are added or removed --}}
    <div class="container px-4 pt-5" data-live-listing="category:BTS Merch" data-live-signature="{{ app(\App\Services\LiveUpdateService::class)->listingSignature('category:BTS Merch') }}">

        {{-- Choose Your Bias (stays visible while the category is "coming soon") --}}
        <div class="bias-section mb-4">
            <div class="text-center bias-title mb-4">Choose Your Bias 💜</div>
            @php
            $row1 = [
                ['id'=>'jungkook','name'=>'Jungkook','emoji'=>'🐰'],
                ['id'=>'taehyung','name'=>'Taehyung','emoji'=>'🐯'],
                ['id'=>'jimin',   'name'=>'Jimin',   'emoji'=>'🐥'],
                ['id'=>'namjoon', 'name'=>'Namjoon', 'emoji'=>'🐨'],
                ['id'=>'jin',     'name'=>'Jin',     'emoji'=>'🐹'],
            ];
            $row2 = [
                ['id'=>'jhope','name'=>'J-Hope','emoji'=>'🐿️'],
                ['id'=>'suga', 'name'=>'Suga',  'emoji'=>'🐱'],
                ['id'=>'ot7',  'name'=>'OT7',   'emoji'=>'💜'],
                ['id'=>'bt21', 'name'=>'BT21',  'emoji'=>'✨'],
            ];
            @endphp
            <div class="bias-grid">
            {{-- Row 1: 5 members --}}
            <div class="bias-grid-row mb-3">
                @foreach($row1 as $m)
                <div class="bias-btn" data-bias="{{ $m['id'] }}" onclick="selectBias('{{ $m['id'] }}')">
                    {{-- <span class="bias-heart">♡</span> --}}
                    <span class="bias-emoji">{{ $m['emoji'] }}</span>
                    <span class="bias-name">{{ $m['name'] }}</span>
                </div>
                @endforeach
            </div>
            {{-- Row 2: 4 members --}}
            <div class="bias-grid-row">
                @foreach($row2 as $m)
                <div class="bias-btn" data-bias="{{ $m['id'] }}" onclick="selectBias('{{ $m['id'] }}')">
                    {{-- <span class="bias-heart">♡</span> --}}
                    <span class="bias-emoji">{{ $m['emoji'] }}</span>
                    <span class="bias-name">{{ $m['name'] }}</span>
                </div>
                @endforeach
            </div>
            </div>
        </div>

        @if($comingSoon ?? false)
            <x-coming-soon title="Our BTS merch is on the way"
                           message="We're putting the finishing touches on the mystery scoops and balls for ARMY. Check back soon!"
                           icon="ri-star-line" accent="#7c3aed" accent2="#a855f7" />
        @else

        {{-- One panel per member, shown when that bias is picked: the member's real products (read from the
             database by HomeController::btsMerch), or "Coming soon" while they have none. --}}
        @php
            $cardFallbacks = [
                'scoop' => 'https://images.unsplash.com/photo-1765516833058-c322c800fee1?w=600&q=80',
                'balls' => 'https://images.unsplash.com/photo-1642761450221-cad688ceafe4?w=600&q=80',
            ];
            $money = fn ($amount) => 'PKR ' . number_format($amount);
        @endphp
        <div id="biasProducts">
            @foreach(array_merge($row1, $row2) as $m)
                @php $cards = array_filter($biasProducts[$m['id']] ?? []); @endphp
                <div class="bias-products" data-bias-products="{{ $m['id'] }}">
                    @if($cards)
                        <div class="row g-4 justify-content-center mb-2">
                            @foreach($cards as $kind => $card)
                                <div class="col-sm-10 col-md-6 col-lg-5">
                                    <div class="bts-card card h-100" data-product="{{ $card['id'] }}">
                                        <div class="position-relative">
                                            <img src="{{ $card['image'] ?? $cardFallbacks[$kind] }}" alt="{{ $card['name'] }}" class="bts-card-img">
                                            @if($kind === 'balls')
                                                <span class="prod-badge badge-min">Min {{ $card['min'] ?? 5 }} pcs</span>
                                            @else
                                                <span class="prod-badge badge-army">ARMY Special</span>
                                            @endif
                                        </div>
                                        <div class="card-body p-4 d-flex flex-column">
                                            <h5 class="fw-bold mb-1">{{ $card['name'] }} 💜</h5>
                                            @if($card['description'])
                                                <p class="text-muted small mb-3">{{ $card['description'] }}</p>
                                            @endif
                                            @if($card['price'])
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <span class="text-muted small">{{ $card['price']['label'] }}</span>
                                                    <span class="fw-bold" style="color:#7c3aed;">{{ $money($card['price']['amount']) }}@if($card['price']['original'] && $card['price']['original'] > $card['price']['amount']) <s style="color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">{{ $money($card['price']['original']) }}</s>@endif</span>
                                                </div>
                                            @endif
                                            <a href="{{ $card['url'] }}" class="bts-explore-btn w-100 justify-content-center mt-auto text-decoration-none">
                                                Order {{ $kind === 'balls' ? 'Mystery Balls' : 'Mystery Scoop' }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-coming-soon :title="$m['name'] . '\'s merch is on the way'"
                                       :message="'We\'re getting ' . $m['name'] . '\'s mystery scoops and balls ready. Check back soon!'"
                                       :icon="'ri-star-line'" accent="#7c3aed" accent2="#a855f7" :actions="false" />
                    @endif
                </div>
            @endforeach
        </div>

        @endif
    </div>

    {{-- ── Borahae Banner ── --}}
    <div class="container px-4 pb-5">
        <div class="text-center mt-4 pt-2">
            <span class="borahae-banner"><span>보라해 Borahae 💜 I Purple You</span></span>
        </div>
    </div>

</div>
{{-- @endsection --}}

<script>
    let selectedBias = null;

    function selectBias(id) {
        selectedBias = id;
        document.querySelectorAll('.bias-btn').forEach(b => b.classList.remove('active'));
        document.querySelector(`.bias-btn[data-bias="${id}"]`).classList.add('active');
        updateCards();
        // kept in the URL so Back from a product page returns here with this member still chosen
        history.replaceState(null, '', location.pathname + '?bias=' + encodeURIComponent(id));
    }

    // Shows the picked member's panel (their real products, or "coming soon"). The panels are rendered by the
    // server from the database; there are none while the whole category is "coming soon".
    function updateCards() {
        document.querySelectorAll('.bias-products').forEach(panel => {
            panel.classList.toggle('show', panel.dataset.biasProducts === selectedBias);
        });
    }

    // Coming back (or opening a shared link) with ?bias=… shows that member's cards straight away
    const savedBias = new URLSearchParams(location.search).get('bias');
    if (savedBias && document.querySelector(`.bias-btn[data-bias="${CSS.escape(savedBias)}"]`)) selectBias(savedBias);
</script>
@endsection